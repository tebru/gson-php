<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal\TypeAdapter\Factory;

use Tebru\Gson\Annotation\JsonAdapter;
use Tebru\Gson\Annotation\VirtualProperty;
use Tebru\Gson\Internal\ConstructorConstructor;
use Tebru\Gson\Internal\Data\ClassMetadataFactory;
use Tebru\Gson\Internal\Excluder;
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
     * @var ClassMetadataFactory
     */
    private $classMetadataFactory;

    /**
     * @var Excluder
     */
    private $excluder;

    /**
     * Constructor
     *
     * @param ConstructorConstructor $constructorConstructor
     * @param ClassMetadataFactory $propertyCollectionFactory
     * @param Excluder $excluder
     */
    public function __construct(
        ConstructorConstructor $constructorConstructor,
        ClassMetadataFactory $propertyCollectionFactory,
        Excluder $excluder
    ) {
        $this->constructorConstructor = $constructorConstructor;
        $this->classMetadataFactory = $propertyCollectionFactory;
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
     */
    public function create(TypeToken $type, TypeAdapterProvider $typeAdapterProvider): TypeAdapter
    {
        $classMetadata = $this->classMetadataFactory->create($type);

        // if class uses a JsonAdapter annotation, use that instead
        /** @var JsonAdapter $jsonAdapterAnnotation */
        $jsonAdapterAnnotation = $classMetadata->getAnnotation(JsonAdapter::class);
        if ($jsonAdapterAnnotation !== null) {
            return $typeAdapterProvider->getAdapterFromAnnotation($type, $jsonAdapterAnnotation);
        }

        $objectConstructor = $this->constructorConstructor->get($type);
        $classVirtualProperty = $classMetadata->getAnnotation(VirtualProperty::class);

        return new ReflectionTypeAdapter(
            $objectConstructor,
            $classMetadata,
            $this->excluder,
            $typeAdapterProvider,
            $classVirtualProperty ? $classVirtualProperty->getValue() : null
        );
    }
}
