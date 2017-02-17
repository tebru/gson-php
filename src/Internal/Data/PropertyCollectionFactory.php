<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal\Data;

use Doctrine\Common\Cache\Cache;
use ReflectionClass;
use ReflectionProperty;
use Tebru\Gson\Annotation\JsonAdapter;
use Tebru\Gson\Annotation\VirtualProperty;
use Tebru\Gson\Internal\AccessorMethodProvider;
use Tebru\Gson\Internal\AccessorStrategy\GetByMethod;
use Tebru\Gson\Internal\AccessorStrategy\SetByNull;
use Tebru\Gson\Internal\AccessorStrategyFactory;
use Tebru\Gson\Internal\Excluder;
use Tebru\Gson\Internal\Naming\PropertyNamer;
use Tebru\Gson\PhpType;
use Tebru\Gson\Internal\PhpTypeFactory;
use Tebru\Gson\Internal\TypeAdapterProvider;

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
     * @param TypeAdapterProvider $typeAdapterProvider
     * @return PropertyCollection
     * @throws \RuntimeException If the value is not valid
     * @throws \Tebru\Gson\Exception\MalformedTypeException If the type cannot be parsed
     * @throws \InvalidArgumentException if the type cannot be handled by a type adapter
     * @throws \InvalidArgumentException If the type does not exist
     */
    public function create(PhpType $phpType, TypeAdapterProvider $typeAdapterProvider): PropertyCollection
    {
        $class = $phpType->getClass();

        $data = $this->cache->fetch($class);
        if (false !== $data) {
            return $data;
        }

        $reflectionClass = new ReflectionClass($class);
        $reflectionProperties = $this->reflectionPropertySetFactory->create($reflectionClass);
        $properties = new PropertyCollection();

        /** @var ReflectionProperty $reflectionProperty */
        foreach ($reflectionProperties as $reflectionProperty) {
            $annotations = $this->annotationCollectionFactory->createPropertyAnnotations(
                $reflectionProperty->getDeclaringClass()->getName(),
                $reflectionProperty->getName()
            );

            $skipSerialize = $this->excludeProperty($reflectionProperty->getDeclaringClass()->getName(), $reflectionProperty->getModifiers(), $annotations, true);
            $skipDeserialize = $this->excludeProperty($reflectionProperty->getDeclaringClass()->getName(), $reflectionProperty->getModifiers(), $annotations, false);

            // if we're skipping serialization and deserialization, we don't need
            // to add the property to the collection
            if ($skipSerialize && $skipDeserialize) {
                continue;
            }

            $serializedName = $this->propertyNamer->serializedName($reflectionProperty->getName(), $annotations, AnnotationSet::TYPE_PROPERTY);
            $getterMethod = $this->accessorMethodProvider->getterMethod($reflectionClass, $reflectionProperty, $annotations);
            $setterMethod = $this->accessorMethodProvider->setterMethod($reflectionClass, $reflectionProperty, $annotations);
            $getterStrategy = $this->accessorStrategyFactory->getterStrategy($reflectionProperty, $getterMethod);
            $setterStrategy = $this->accessorStrategyFactory->setterStrategy($reflectionProperty, $setterMethod);
            $type = $this->phpTypeFactory->create($annotations, AnnotationSet::TYPE_PROPERTY, $getterMethod, $setterMethod);

            /** @var JsonAdapter $jsonAdapterAnnotation */
            $jsonAdapterAnnotation = $annotations->getAnnotation(JsonAdapter::class, AnnotationSet::TYPE_PROPERTY);
            $adapter = null !== $jsonAdapterAnnotation
                ? $typeAdapterProvider->getAdapterFromAnnotation($type, $jsonAdapterAnnotation)
                : $typeAdapterProvider->getAdapter($type);

            $property = new Property(
                $reflectionProperty->getDeclaringClass()->getName(),
                $reflectionProperty->getName(),
                $serializedName,
                $type,
                $getterStrategy,
                $setterStrategy,
                $annotations,
                $reflectionProperty->getModifiers(),
                $adapter
            );

            $property->setSkipSerialize($skipSerialize);
            $property->setSkipDeserialize($skipDeserialize);

            $properties->add($property);
        }

        // add virtual properties
        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            $annotations = $this->annotationCollectionFactory->createMethodAnnotations($reflectionMethod->getDeclaringClass()->getName(), $reflectionMethod->getName());
            if (null === $annotations->getAnnotation(VirtualProperty::class, AnnotationSet::TYPE_METHOD)) {
                continue;
            }

            $skipSerialize = $this->excludeProperty($reflectionMethod->getDeclaringClass()->getName(), ReflectionProperty::IS_PUBLIC, $annotations, true);
            $skipDeserialize = $this->excludeProperty($reflectionMethod->getDeclaringClass()->getName(), ReflectionProperty::IS_PUBLIC, $annotations, false);

            // if we're skipping serialization and deserialization, we don't need
            // to add the property to the collection
            if ($skipSerialize && $skipDeserialize) {
                continue;
            }

            $serializedName = $this->propertyNamer->serializedName($reflectionMethod->getName(), $annotations, AnnotationSet::TYPE_METHOD);
            $type = $this->phpTypeFactory->create($annotations, AnnotationSet::TYPE_METHOD, $reflectionMethod);
            $getterStrategy = new GetByMethod($reflectionMethod->getName());
            $setterStrategy = new SetByNull();

            /** @var JsonAdapter $jsonAdapterAnnotation */
            $jsonAdapterAnnotation = $annotations->getAnnotation(JsonAdapter::class, AnnotationSet::TYPE_METHOD);
            $adapter = null !== $jsonAdapterAnnotation
                ? $typeAdapterProvider->getAdapterFromAnnotation($type, $jsonAdapterAnnotation)
                : $typeAdapterProvider->getAdapter($type);

            $property = new Property(
                $reflectionMethod->getDeclaringClass()->getName(),
                $reflectionMethod->getName(),
                $serializedName,
                $type,
                $getterStrategy,
                $setterStrategy,
                $annotations,
                $reflectionMethod->getModifiers(),
                $adapter
            );

            $property->setSkipSerialize($skipSerialize);
            $property->setSkipDeserialize($skipDeserialize);

            $properties->add($property);
        }

        $this->cache->save($class, $properties);

        return $properties;
    }

    /**
     * Returns true if we should skip this property
     *
     * Asks the excluder if we should skip the property or class
     *
     * @param string $propertyClassName
     * @param int $propertyModifiers
     * @param AnnotationSet $annotations
     * @param bool $serialize
     * @return bool
     * @throws \InvalidArgumentException If the type does not exist
     */
    private function excludeProperty(string $propertyClassName, int $propertyModifiers, AnnotationSet $annotations, bool $serialize): bool
    {
        $excludeClass = false;
        if (null !== $propertyClassName) {
            $excludeClass = $this->excluder->excludeClass($propertyClassName, $serialize);
        }

        $excludeProperty = $this->excluder->excludeProperty($propertyModifiers, $annotations, $serialize);

        return $excludeClass || $excludeProperty;
    }
}
