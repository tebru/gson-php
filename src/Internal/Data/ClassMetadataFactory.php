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
use Tebru\Gson\ClassMetadata;
use Tebru\Gson\Internal\AccessorMethodProvider;
use Tebru\Gson\Internal\AccessorStrategy\GetByMethod;
use Tebru\Gson\Internal\AccessorStrategy\SetByNull;
use Tebru\Gson\Internal\AccessorStrategyFactory;
use Tebru\Gson\Internal\DefaultClassMetadata;
use Tebru\Gson\Internal\Excluder;
use Tebru\Gson\Internal\Naming\PropertyNamer;
use Tebru\Gson\Internal\TypeTokenFactory;
use Tebru\PhpType\TypeToken;

/**
 * Class PropertyCollectionFactory
 *
 * Aggregates information about class properties to be used during
 * future parsing.
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class ClassMetadataFactory
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
     * @var TypeTokenFactory
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
     * @param PropertyNamer $propertyNamer
     * @param AccessorMethodProvider $accessorMethodProvider
     * @param AccessorStrategyFactory $accessorStrategyFactory
     * @param TypeTokenFactory $phpTypeFactory
     * @param Excluder $excluder
     * @param CacheInterface $cache
     */
    public function __construct(
        ReflectionPropertySetFactory $reflectionPropertySetFactory,
        AnnotationReaderAdapter $annotationReader,
        PropertyNamer $propertyNamer,
        AccessorMethodProvider $accessorMethodProvider,
        AccessorStrategyFactory $accessorStrategyFactory,
        TypeTokenFactory $phpTypeFactory,
        Excluder $excluder,
        CacheInterface $cache
    ) {
        $this->reflectionPropertySetFactory = $reflectionPropertySetFactory;
        $this->annotationReader = $annotationReader;
        $this->propertyNamer = $propertyNamer;
        $this->accessorMethodProvider = $accessorMethodProvider;
        $this->accessorStrategyFactory = $accessorStrategyFactory;
        $this->phpTypeFactory = $phpTypeFactory;
        $this->excluder = $excluder;
        $this->cache = $cache;
    }

    /**
     * Create a new [@see ClassMetadata] with properties of the provided type
     *
     * @param TypeToken $phpType
     * @return DefaultClassMetadata
     */
    public function create(TypeToken $phpType): DefaultClassMetadata
    {
        $class = $phpType->rawType;
        $key = 'gson.classmetadata.'.str_replace('\\', '', $class);

        $data = $this->cache->get($key);
        if ($data !== null) {
            return $data;
        }

        $properties = new PropertyCollection();
        $classMetadata = new DefaultClassMetadata($class, $this->annotationReader->readClass($class, true), $properties);

        $reflectionClass = new ReflectionClass($class);
        $reflectionProperties = $this->reflectionPropertySetFactory->create($reflectionClass);

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
                false,
                $classMetadata
            );

            $skipSerialize = $this->excluder->excludePropertySerialize($property);
            $skipDeserialize = $this->excluder->excludePropertyDeserialize($property);

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
                true,
                $classMetadata
            );

            $skipSerialize = $this->excluder->excludePropertySerialize($property);
            $skipDeserialize = $this->excluder->excludePropertyDeserialize($property);

            // if we're skipping serialization and deserialization, we don't need
            // to add the property to the collection
            if ($skipSerialize && $skipDeserialize) {
                continue;
            }

            $property->setSkipSerialize($skipSerialize);
            $property->setSkipDeserialize($skipDeserialize);

            $properties->add($property);
        }

        $classMetadata->setSkipSerialize($this->excluder->excludeClassSerialize($classMetadata));
        $classMetadata->setSkipDeserialize($this->excluder->excludeClassDeserialize($classMetadata));

        $this->cache->set($key, $classMetadata);

        return $classMetadata;
    }
}
