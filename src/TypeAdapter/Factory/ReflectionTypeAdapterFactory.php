<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\TypeAdapter\Factory;

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
    protected $constructorConstructor;

    /**
     * @var ClassMetadataFactory
     */
    protected $classMetadataFactory;

    /**
     * @var Excluder
     */
    private $excluder;

    /**
     * @var ClassMetadataVisitor[]
     */
    protected $classMetadataVisitors;

    /**
     * Constructor
     *
     * @param ConstructorConstructor $constructorConstructor
     * @param ClassMetadataFactory $propertyCollectionFactory
     * @param Excluder $excluder
     * @param ClassMetadataVisitor[] $classMetadataVisitors
     */
    public function __construct(
        ConstructorConstructor $constructorConstructor,
        ClassMetadataFactory $propertyCollectionFactory,
        Excluder $excluder,
        array $classMetadataVisitors
    ) {
        $this->constructorConstructor = $constructorConstructor;
        $this->classMetadataFactory = $propertyCollectionFactory;
        $this->classMetadataVisitors = $classMetadataVisitors;
        $this->excluder = $excluder;
    }

    /**
     * Accepts the current type and a [@see TypeAdapterProvider] in case another type adapter needs
     * to be fetched during creation.  Should return a new instance of the TypeAdapter. Will return
     * null if the type adapter is not supported for the provided type.
     *
     * @param TypeToken $type
     * @param TypeAdapterProvider $typeAdapterProvider
     * @return TypeAdapter|null
     */
    public function create(TypeToken $type, TypeAdapterProvider $typeAdapterProvider): ?TypeAdapter
    {
        if (!$type->phpType === TypeToken::OBJECT || !class_exists($type->rawType)) {
            return null;
        }

        $classMetadata = $this->classMetadataFactory->create($type, $typeAdapterProvider);
        if ($this->excluder->hasRuntimeClassStrategies($classMetadata)) {
            $classMetadata->hasExclusions = true;
        }

        foreach ($this->classMetadataVisitors as $visitor) {
            $visitor->onLoaded($classMetadata);
        }

        foreach ($classMetadata->properties as $property) {
            if ($this->excluder->hasRuntimePropertyStrategies($property)) {
                $property->hasExclusions = true;
            }
        }

        // if class uses a JsonAdapter annotation, use that instead
        /** @var JsonAdapter $jsonAdapterAnnotation */
        $jsonAdapterAnnotation = $classMetadata->annotations->get(JsonAdapter::class);
        if ($jsonAdapterAnnotation !== null) {
            return $typeAdapterProvider->getAdapterFromAnnotation($type, $jsonAdapterAnnotation);
        }

        $objectConstructor = $this->constructorConstructor->get($type);
        $classVirtualProperty = $classMetadata->annotations->get(VirtualProperty::class);
        $classVirtualProperty = $classVirtualProperty ? $classVirtualProperty->getValue() : null;

        return new ReflectionTypeAdapter($objectConstructor, $classMetadata, $classVirtualProperty);
    }

    /**
     * Return true if object can be written to disk
     *
     * @return bool
     */
    public function canCache(): bool
    {
        return false;
    }
}
