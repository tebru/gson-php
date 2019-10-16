<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\TypeAdapter\Factory;

use Tebru\Gson\Annotation\ExclusionCheck;
use Tebru\Gson\Annotation\JsonAdapter;
use Tebru\Gson\Annotation\VirtualProperty;
use Tebru\Gson\ClassMetadataVisitor;
use Tebru\Gson\Internal\ConstructorConstructor;
use Tebru\Gson\Internal\Data\ClassMetadataFactory;
use Tebru\Gson\Internal\Excluder;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\TypeAdapter;
use Tebru\Gson\TypeAdapter\ReflectionTypeAdapter;
use Tebru\Gson\TypeAdapterFactory;
use Tebru\PhpType\TypeToken;

/**
 * Class ReflectionTypeAdapterFactory
 *
 * @author Nate Brunette <n@tebru.net>
 */
class ReflectionTypeAdapterFactory implements TypeAdapterFactory
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
     * @var bool
     */
    private $requireExclusionCheck;

    /**
     * @var array
     */
    private $classMetadataVisitors;

    /**
     * Constructor
     *
     * @param ConstructorConstructor $constructorConstructor
     * @param ClassMetadataFactory $propertyCollectionFactory
     * @param Excluder $excluder
     * @param bool $requireExclusionCheck
     * @param ClassMetadataVisitor[] $classMetadataVisitors
     */
    public function __construct(
        ConstructorConstructor $constructorConstructor,
        ClassMetadataFactory $propertyCollectionFactory,
        Excluder $excluder,
        bool $requireExclusionCheck,
        array $classMetadataVisitors
    ) {
        $this->constructorConstructor = $constructorConstructor;
        $this->classMetadataFactory = $propertyCollectionFactory;
        $this->excluder = $excluder;
        $this->requireExclusionCheck = $requireExclusionCheck;
        $this->classMetadataVisitors = $classMetadataVisitors;
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

        foreach ($this->classMetadataVisitors as $visitor) {
            $visitor->onLoaded($classMetadata);
        }

        // if class uses a JsonAdapter annotation, use that instead
        /** @var JsonAdapter $jsonAdapterAnnotation */
        $jsonAdapterAnnotation = $classMetadata->getAnnotation(JsonAdapter::class);
        if ($jsonAdapterAnnotation !== null) {
            return $typeAdapterProvider->getAdapterFromAnnotation($type, $jsonAdapterAnnotation);
        }

        $objectConstructor = $this->constructorConstructor->get($type);
        $classVirtualProperty = $classMetadata->getAnnotation(VirtualProperty::class);

        $propertyExclusionCheck = false;
        if ($this->requireExclusionCheck) {
            foreach ($classMetadata->getPropertyCollection()->toArray() as $property) {
                if ($property->getAnnotation(ExclusionCheck::class) !== null) {
                    $propertyExclusionCheck = true;
                    break;
                }
            }
        }

        return new ReflectionTypeAdapter(
            $objectConstructor,
            $classMetadata,
            $this->excluder,
            $typeAdapterProvider,
            $classVirtualProperty ? $classVirtualProperty->getValue() : null,
            $this->requireExclusionCheck,
            $propertyExclusionCheck
        );
    }
}
