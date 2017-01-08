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
     * @param Cache $cache
     */
    public function __construct(
        ReflectionPropertySetFactory $reflectionPropertySetFactory,
        AnnotationCollectionFactory $annotationCollectionFactory,
        PropertyNamer $propertyNamer,
        AccessorMethodProvider $accessorMethodProvider,
        AccessorStrategyFactory $accessorStrategyFactory,
        PhpTypeFactory $phpTypeFactory,
        Cache $cache
    ) {
        $this->reflectionPropertySetFactory = $reflectionPropertySetFactory;
        $this->annotationCollectionFactory = $annotationCollectionFactory;
        $this->propertyNamer = $propertyNamer;
        $this->accessorMethodProvider = $accessorMethodProvider;
        $this->accessorStrategyFactory = $accessorStrategyFactory;
        $this->phpTypeFactory = $phpTypeFactory;
        $this->cache = $cache;
    }

    /**
     * Create a [@see PropertyCollection] based on the properties of the provided type
     *
     * @param PhpType $phpType
     * @return PropertyCollection
     */
    public function create(PhpType $phpType): PropertyCollection
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
            $annotations = $this->annotationCollectionFactory->create($reflectionProperty);
            $serializedName = $this->propertyNamer->serializedName($reflectionProperty, $annotations);
            $getterMethod = $this->accessorMethodProvider->getterMethod($reflectionClass, $reflectionProperty, $annotations);
            $setterMethod = $this->accessorMethodProvider->setterMethod($reflectionClass, $reflectionProperty, $annotations);
            $getterStrategy = $this->accessorStrategyFactory->getterStrategy($reflectionProperty, $getterMethod);
            $setterStrategy = $this->accessorStrategyFactory->setterStrategy($reflectionProperty, $setterMethod);
            $type = $this->phpTypeFactory->create($annotations, $getterMethod, $setterMethod);

            $properties->add(new Property($reflectionProperty->getName(), $serializedName, $type, $getterStrategy, $setterStrategy));
        }

        $this->cache->save($class, $properties);

        return $properties;
    }
}
