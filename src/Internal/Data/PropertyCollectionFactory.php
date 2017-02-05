<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal\Data;

use Doctrine\Common\Cache\Cache;
use ReflectionClass;
use ReflectionProperty;
use Tebru\Gson\Internal\AccessorMethodProvider;
use Tebru\Gson\Internal\AccessorStrategyFactory;
use Tebru\Gson\Internal\Excluder;
use Tebru\Gson\Internal\Naming\PropertyNamer;
use Tebru\Gson\Internal\PhpType;
use Tebru\Gson\Internal\PhpTypeFactory;

/**
 * Class PropertyCollectionFactory
 *
 * Aggregates information about class properties to be used during
 * future parsing.
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class PropertyCollectionFactory
{
    /**
     * @var ReflectionPropertySetFactory
     */
    private $reflectionPropertySetFactory;

    /**
     * @var AnnotationCollectionFactory
     */
    private $annotationCollectionFactory;

    /**
     * @var PropertyNamer
     */
    private $propertyNamer;

    /**
     * @var AccessorMethodProvider
     */
    private $accessorMethodProvider;

    /**
     * @var AccessorStrategyFactory
     */
    private $accessorStrategyFactory;

    /**
     * @var PhpTypeFactory
     */
    private $phpTypeFactory;

    /**
     * @var Excluder
     */
    private $excluder;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var array
     */
    private $collectionCache = [];

    /**
     * Constructor
     *
     * @param ReflectionPropertySetFactory $reflectionPropertySetFactory
     * @param AnnotationCollectionFactory $annotationCollectionFactory
     * @param PropertyNamer $propertyNamer
     * @param AccessorMethodProvider $accessorMethodProvider
     * @param AccessorStrategyFactory $accessorStrategyFactory
     * @param PhpTypeFactory $phpTypeFactory
     * @param Excluder $excluder
     * @param Cache $cache
     */
    public function __construct(
        ReflectionPropertySetFactory $reflectionPropertySetFactory,
        AnnotationCollectionFactory $annotationCollectionFactory,
        PropertyNamer $propertyNamer,
        AccessorMethodProvider $accessorMethodProvider,
        AccessorStrategyFactory $accessorStrategyFactory,
        PhpTypeFactory $phpTypeFactory,
        Excluder $excluder,
        Cache $cache
    ) {
        $this->reflectionPropertySetFactory = $reflectionPropertySetFactory;
        $this->annotationCollectionFactory = $annotationCollectionFactory;
        $this->propertyNamer = $propertyNamer;
        $this->accessorMethodProvider = $accessorMethodProvider;
        $this->accessorStrategyFactory = $accessorStrategyFactory;
        $this->phpTypeFactory = $phpTypeFactory;
        $this->excluder = $excluder;
        $this->cache = $cache;
    }

    /**
     * Create a [@see PropertyCollection] based on the properties of the provided type
     *
     * @param PhpType $phpType
     * @return PropertyCollection
     * @throws \RuntimeException If the value is not valid
     * @throws \Tebru\Gson\Exception\MalformedTypeException If the type cannot be parsed
     */
    public function create(PhpType $phpType): PropertyCollection
    {
        $class = $phpType->getClass();

        if (array_key_exists($class, $this->collectionCache)) {
            return $this->collectionCache[$class];
        }

        $data = $this->cache->fetch($class);
        if (false !== $data) {
            $this->collectionCache[$class] = $data;
            return $data;
        }

        $reflectionClass = new ReflectionClass($class);
        $reflectionProperties = $this->reflectionPropertySetFactory->create($reflectionClass);
        $properties = new PropertyCollection();

        /** @var ReflectionProperty $reflectionProperty */
        foreach ($reflectionProperties as $reflectionProperty) {
            $annotations = $this->annotationCollectionFactory->createPropertyAnnotations($reflectionProperty);
            $serializedName = $this->propertyNamer->serializedName($reflectionProperty, $annotations);
            $getterMethod = $this->accessorMethodProvider->getterMethod($reflectionClass, $reflectionProperty, $annotations);
            $setterMethod = $this->accessorMethodProvider->setterMethod($reflectionClass, $reflectionProperty, $annotations);
            $getterStrategy = $this->accessorStrategyFactory->getterStrategy($reflectionProperty, $getterMethod);
            $setterStrategy = $this->accessorStrategyFactory->setterStrategy($reflectionProperty, $setterMethod);
            $type = $this->phpTypeFactory->create($annotations, $getterMethod, $setterMethod);

            $property = new Property(
                $reflectionProperty->getDeclaringClass()->getName(),
                $reflectionProperty->getName(),
                $serializedName,
                $type,
                $getterStrategy,
                $setterStrategy,
                $annotations,
                $reflectionProperty->getModifiers()
            );

            $skipSerialize = $this->excludeProperty($property, true);
            $skipDeserialize = $this->excludeProperty($property, false);

            // if we're skipping serialization and deserialization, we don't need
            // to add the property to the collection
            if ($skipSerialize && $skipDeserialize) {
                continue;
            }

            $property->setSkipSerialize($skipSerialize);
            $property->setSkipDeserialize($skipDeserialize);

            $properties->add($property);
        }

        $this->collectionCache[$class] = $properties;
        $this->cache->save($class, $properties);

        return $properties;
    }

    /**
     * Returns true if we should skip this property
     *
     * Asks the excluder if we should skip the property or class
     *
     * @param Property $property
     * @param bool $serialize
     * @return bool
     */
    private function excludeProperty(Property $property, bool $serialize): bool
    {
        $excludeClass = false;
        $class = $property->getType()->getClass();
        if (null !== $class) {
            $excludeClass = $this->excluder->excludeClass($class, $serialize);
        }

        $excludeProperty = $this->excluder->excludeProperty($property, $serialize);

        return $excludeClass || $excludeProperty;
    }
}
