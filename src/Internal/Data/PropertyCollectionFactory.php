<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal\Data;

use Psr\SimpleCache\CacheInterface;
use ReflectionClass;
use ReflectionProperty;
use Tebru\AnnotationReader\AnnotationReaderAdapter;
use Tebru\Gson\Annotation\VirtualProperty;
use Tebru\Gson\Internal\AccessorMethodProvider;
use Tebru\Gson\Internal\AccessorStrategy\GetByMethod;
use Tebru\Gson\Internal\AccessorStrategy\SetByNull;
use Tebru\Gson\Internal\AccessorStrategyFactory;
use Tebru\Gson\Internal\Excluder;
use Tebru\Gson\Internal\MetadataFactory;
use Tebru\Gson\Internal\Naming\PropertyNamer;
use Tebru\Gson\Internal\PhpTypeFactory;
use Tebru\Gson\PropertyMetadata;
use Tebru\PhpType\TypeToken;

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
     * @var AnnotationReaderAdapter
     */
    private $annotationReader;

    /**
     * @var MetadataFactory
     */
    private $metadataFactory;

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
     * @var CacheInterface
     */
    private $cache;

    /**
     * Constructor
     *
     * @param ReflectionPropertySetFactory $reflectionPropertySetFactory
     * @param AnnotationReaderAdapter $annotationReader
     * @param MetadataFactory $metadataFactory
     * @param PropertyNamer $propertyNamer
     * @param AccessorMethodProvider $accessorMethodProvider
     * @param AccessorStrategyFactory $accessorStrategyFactory
     * @param PhpTypeFactory $phpTypeFactory
     * @param Excluder $excluder
     * @param CacheInterface $cache
     */
    public function __construct(
        ReflectionPropertySetFactory $reflectionPropertySetFactory,
        AnnotationReaderAdapter $annotationReader,
        MetadataFactory $metadataFactory,
        PropertyNamer $propertyNamer,
        AccessorMethodProvider $accessorMethodProvider,
        AccessorStrategyFactory $accessorStrategyFactory,
        PhpTypeFactory $phpTypeFactory,
        Excluder $excluder,
        CacheInterface $cache
    ) {
        $this->reflectionPropertySetFactory = $reflectionPropertySetFactory;
        $this->annotationReader = $annotationReader;
        $this->metadataFactory = $metadataFactory;
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
     * @param TypeToken $phpType
     * @return PropertyCollection
     */
    public function create(TypeToken $phpType): PropertyCollection
    {
        $class = $phpType->getRawType();
        $key = 'gson.properties.'.\str_replace('\\', '', $class);

        $data = $this->cache->get($key);
        if ($data !== null) {
            return $data;
        }

        $reflectionClass = new ReflectionClass($class);
        $reflectionProperties = $this->reflectionPropertySetFactory->create($reflectionClass);
        $properties = new PropertyCollection();

        /** @var ReflectionProperty $reflectionProperty */
        foreach ($reflectionProperties as $reflectionProperty) {
            $annotations = $this->annotationReader->readProperty(
                $reflectionProperty->getName(),
                $reflectionProperty->getDeclaringClass()->getName(),
                false,
                true
            );

            $serializedName = $this->propertyNamer->serializedName($reflectionProperty->getName(), $annotations);
            $getterMethod = $this->accessorMethodProvider->getterMethod($reflectionClass, $reflectionProperty, $annotations);
            $setterMethod = $this->accessorMethodProvider->setterMethod($reflectionClass, $reflectionProperty, $annotations);
            $getterStrategy = $this->accessorStrategyFactory->getterStrategy($reflectionProperty, $getterMethod);
            $setterStrategy = $this->accessorStrategyFactory->setterStrategy($reflectionProperty, $setterMethod);
            $type = $this->phpTypeFactory->create($annotations, $getterMethod, $setterMethod, $reflectionProperty);

            $property = new Property(
                $reflectionProperty->getName(),
                $serializedName,
                $type,
                $getterStrategy,
                $setterStrategy,
                $annotations,
                $reflectionProperty->getModifiers(),
                false
            );

            $classMetadata = $this->metadataFactory->createClassMetadata($reflectionProperty->getDeclaringClass()->getName());
            $propertyMetadata = $this->metadataFactory->createPropertyMetadata($property, $classMetadata);

            $skipSerialize = $this->excludeProperty($propertyMetadata, true);
            $skipDeserialize = $this->excludeProperty($propertyMetadata, false);

            // if we're skipping serialization and deserialization, we don't need
            // to add the property to the collection
            if ($skipSerialize && $skipDeserialize) {
                continue;
            }

            $property->setSkipSerialize($skipSerialize);
            $property->setSkipDeserialize($skipDeserialize);

            $properties->add($property);
        }

        // add virtual properties
        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            $annotations = $this->annotationReader->readMethod(
                $reflectionMethod->getName(),
                $reflectionMethod->getDeclaringClass()->getName(),
                false,
                true
            );
            if (null === $annotations->get(VirtualProperty::class)) {
                continue;
            }

            $serializedName = $this->propertyNamer->serializedName($reflectionMethod->getName(), $annotations);
            $type = $this->phpTypeFactory->create($annotations, $reflectionMethod);
            $getterStrategy = new GetByMethod($reflectionMethod->getName());
            $setterStrategy = new SetByNull();

            $property = new Property(
                $reflectionMethod->getName(),
                $serializedName,
                $type,
                $getterStrategy,
                $setterStrategy,
                $annotations,
                $reflectionMethod->getModifiers(),
                true
            );

            $classMetadata = $this->metadataFactory->createClassMetadata($reflectionMethod->getDeclaringClass()->getName());
            $propertyMetadata = $this->metadataFactory->createPropertyMetadata($property, $classMetadata);

            $skipSerialize = $this->excludeProperty($propertyMetadata, true);
            $skipDeserialize = $this->excludeProperty($propertyMetadata, false);

            // if we're skipping serialization and deserialization, we don't need
            // to add the property to the collection
            if ($skipSerialize && $skipDeserialize) {
                continue;
            }

            $property->setSkipSerialize($skipSerialize);
            $property->setSkipDeserialize($skipDeserialize);

            $properties->add($property);
        }

        $this->cache->set($key, $properties);

        return $properties;
    }

    /**
     * Returns true if we should skip this property
     *
     * Asks the excluder if we should skip the property or class
     *
     * @param PropertyMetadata $propertyMetadata
     * @param bool $serialize
     * @return bool
     */
    private function excludeProperty(PropertyMetadata $propertyMetadata, bool $serialize): bool
    {
        $excludeClass = $this->excluder->excludeClass($propertyMetadata->getDeclaringClassMetadata(), $serialize);
        $excludeProperty = $this->excluder->excludeProperty($propertyMetadata, $serialize);

        return $excludeClass || $excludeProperty;
    }
}
