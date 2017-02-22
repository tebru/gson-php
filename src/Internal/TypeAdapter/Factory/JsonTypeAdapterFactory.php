<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal\TypeAdapter\Factory;

use Tebru\Gson\Annotation\JsonAdapter;
use Tebru\Gson\Internal\Data\AnnotationCollectionFactory;
use Tebru\Gson\Internal\Data\AnnotationSet;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\PhpType;
use Tebru\Gson\TypeAdapter;
use Tebru\Gson\TypeAdapterFactory;

/**
 * Class JsonTypeAdapterFactory
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class JsonTypeAdapterFactory implements TypeAdapterFactory
{
    /**
     * @var AnnotationCollectionFactory
     */
    private $annotationCollectionFactory;

    /**
     * Constructor
     *
     * @param AnnotationCollectionFactory $annotationCollectionFactory
     */
    public function __construct(AnnotationCollectionFactory $annotationCollectionFactory)
    {
        $this->annotationCollectionFactory = $annotationCollectionFactory;
    }

    /**
     * Will be called before ::create() is called.  The current type will be passed
     * in.  Return false if ::create() should not be called.
     *
     * @param PhpType $type
     * @return bool
     * @throws \InvalidArgumentException If the type does not exist
     */
    public function supports(PhpType $type): bool
    {
        if (!$type->isObject()) {
            return false;
        }

        $annotations = $this->annotationCollectionFactory->createClassAnnotations($type->getType());

        return null !== $annotations->getAnnotation(JsonAdapter::class, AnnotationSet::TYPE_CLASS);
    }

    /**
     * Accepts the current type and a [@see TypeAdapterProvider] in case another type adapter needs
     * to be fetched during creation.  Should return a new instance of the TypeAdapter.
     *
     * @param PhpType $type
     * @param TypeAdapterProvider $typeAdapterProvider
     * @return TypeAdapter
     * @throws \InvalidArgumentException if an invalid adapter is found
     * @throws \Tebru\Gson\Exception\MalformedTypeException If the type cannot be parsed
     */
    public function create(PhpType $type, TypeAdapterProvider $typeAdapterProvider): TypeAdapter
    {
        $annotations = $this->annotationCollectionFactory->createClassAnnotations($type->getType());

        /** @var JsonAdapter $annotation */
        $annotation = $annotations->getAnnotation(JsonAdapter::class, AnnotationSet::TYPE_CLASS);

        return $typeAdapterProvider->getAdapterFromAnnotation($type, $annotation);
    }
}
