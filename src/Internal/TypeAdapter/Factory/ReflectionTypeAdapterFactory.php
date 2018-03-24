<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal\TypeAdapter\Factory;

use Tebru\Gson\Internal\ConstructorConstructor;
use Tebru\Gson\Internal\Data\MetadataPropertyCollection;
use Tebru\Gson\Internal\Data\Property;
use Tebru\Gson\Internal\Excluder;
use Tebru\Gson\Internal\MetadataFactory;
use Tebru\Gson\Internal\Data\PropertyCollectionFactory;
use Tebru\Gson\Internal\TypeAdapter\ReflectionTypeAdapter;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\TypeAdapter;
use Tebru\Gson\TypeAdapterFactory;
use Tebru\PhpType\TypeToken;

/**
 * Class ReflectionTypeAdapterFactory
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class ReflectionTypeAdapterFactory implements TypeAdapterFactory
{
    /**
     * @var ConstructorConstructor
     */
    private $constructorConstructor;

    /**
     * @var PropertyCollectionFactory
     */
    private $propertyCollectionFactory;

    /**
     * @var MetadataFactory
     */
    private $metadataFactory;

    /**
     * @var Excluder
     */
    private $excluder;

    /**
     * Constructor
     *
     * @param ConstructorConstructor $constructorConstructor
     * @param PropertyCollectionFactory $propertyCollectionFactory
     * @param MetadataFactory $metadataFactory
     * @param Excluder $excluder
     */
    public function __construct(
        ConstructorConstructor $constructorConstructor,
        PropertyCollectionFactory $propertyCollectionFactory,
        MetadataFactory $metadataFactory,
        Excluder $excluder
    ) {
        $this->constructorConstructor = $constructorConstructor;
        $this->propertyCollectionFactory = $propertyCollectionFactory;
        $this->metadataFactory = $metadataFactory;
        $this->excluder = $excluder;
    }

    /**
     * Will be called before ::create() is called.  The current type will be passed
     * in.  Return false if ::create() should not be called.
     *
     * @param TypeToken $type
     * @return bool
     */
    public function supports(TypeToken $type): bool
    {
        if (!$type->isObject()) {
            return false;
        }

        return \class_exists($type->getRawType());
    }

    /**
     * Accepts the current type.  Should return a new instance of the TypeAdapter.
     *
     * @param TypeToken $type
     * @param TypeAdapterProvider $typeAdapterProvider
     * @return TypeAdapter
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function create(TypeToken $type, TypeAdapterProvider $typeAdapterProvider): TypeAdapter
    {
        $properties = $this->propertyCollectionFactory->create($type);
        $objectConstructor = $this->constructorConstructor->get($type);

        $classMetadata = $this->metadataFactory->createClassMetadata($type->getRawType());
        $metadataPropertyCollection = new MetadataPropertyCollection();

        /** @var Property $property */
        foreach ($properties as $property) {
            $metadataPropertyCollection->add($this->metadataFactory->createPropertyMetadata($property, $classMetadata));
        }

        return new ReflectionTypeAdapter(
            $objectConstructor,
            $properties,
            $metadataPropertyCollection,
            $classMetadata,
            $this->excluder,
            $typeAdapterProvider
        );
    }
}
