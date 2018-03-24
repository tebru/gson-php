<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal\TypeAdapter\Factory;

use Tebru\AnnotationReader\AnnotationReaderAdapter;
use Tebru\Gson\Annotation\JsonAdapter;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\TypeAdapter;
use Tebru\Gson\TypeAdapterFactory;
use Tebru\PhpType\TypeToken;

/**
 * Class JsonTypeAdapterFactory
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class JsonTypeAdapterFactory implements TypeAdapterFactory
{
    /**
     * @var AnnotationReaderAdapter
     */
    private $annotationReader;

    /**
     * Constructor
     *
     * @param AnnotationReaderAdapter $annotationReader
     */
    public function __construct(AnnotationReaderAdapter $annotationReader)
    {
        $this->annotationReader = $annotationReader;
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

        if (!\class_exists($type->getRawType())) {
            return false;
        }

        $annotations = $this->annotationReader->readClass($type->getRawType(), true);

        return $annotations->exists(JsonAdapter::class);
    }

    /**
     * Accepts the current type and a [@see TypeAdapterProvider] in case another type adapter needs
     * to be fetched during creation.  Should return a new instance of the TypeAdapter.
     *
     * @param TypeToken $type
     * @param TypeAdapterProvider $typeAdapterProvider
     * @return TypeAdapter
     */
    public function create(TypeToken $type, TypeAdapterProvider $typeAdapterProvider): TypeAdapter
    {
        $annotations = $this->annotationReader->readClass($type->getRawType(), true);

        /** @var JsonAdapter $annotation */
        $annotation = $annotations->get(JsonAdapter::class);

        return $typeAdapterProvider->getAdapterFromAnnotation($type, $annotation);
    }
}
