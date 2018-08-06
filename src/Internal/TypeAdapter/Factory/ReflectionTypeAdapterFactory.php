<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal\TypeAdapter\Factory;

use Tebru\AnnotationReader\AnnotationReaderAdapter;
use Tebru\Gson\Annotation\JsonAdapter;
use Tebru\Gson\Annotation\VirtualProperty;
use Tebru\Gson\Internal\ConstructorConstructor;
use Tebru\Gson\Internal\Data\PropertyCollectionFactory;
use Tebru\Gson\Internal\DefaultClassMetadata;
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
     * @var PropertyCollectionFactory
     */
    private $propertyCollectionFactory;

    /**
     * @var AnnotationReaderAdapter
     */
    private $annotationReader;

    /**
     * @var Excluder
     */
    private $excluder;

    /**
     * Constructor
     *
     * @param ConstructorConstructor $constructorConstructor
     * @param PropertyCollectionFactory $propertyCollectionFactory
     * @param AnnotationReaderAdapter $annotationReader
     * @param Excluder $excluder
     */
    public function __construct(
        ConstructorConstructor $constructorConstructor,
        PropertyCollectionFactory $propertyCollectionFactory,
        AnnotationReaderAdapter $annotationReader,
        Excluder $excluder
    ) {
        $this->constructorConstructor = $constructorConstructor;
        $this->propertyCollectionFactory = $propertyCollectionFactory;
        $this->annotationReader = $annotationReader;
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
        $class = $type->getRawType();
        $classAnnotations = $this->annotationReader->readClass($class, true);

        // if class uses a JsonAdapter annotation, use that instead
        /** @var JsonAdapter $jsonAdapterAnnotation */
        $jsonAdapterAnnotation = $classAnnotations->get(JsonAdapter::class);
        if ($jsonAdapterAnnotation !== null) {
            return $typeAdapterProvider->getAdapterFromAnnotation($type, $jsonAdapterAnnotation);
        }

        $classMetadata = new DefaultClassMetadata($class, $classAnnotations);
        $properties = $this->propertyCollectionFactory->create($type, $classMetadata);
        $objectConstructor = $this->constructorConstructor->get($type);
        $classVirtualProperty = $classMetadata->getAnnotation(VirtualProperty::class);

        $skipSerialize = $this->excluder->excludeClass($classMetadata, true);
        $skipDeserialize = $this->excluder->excludeClass($classMetadata, false);

        return new ReflectionTypeAdapter(
            $objectConstructor,
            $properties,
            $classMetadata,
            $this->excluder,
            $typeAdapterProvider,
            $classVirtualProperty ? $classVirtualProperty->getValue() : null,
            $skipSerialize,
            $skipDeserialize
        );
    }
}
