<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal;

use InvalidArgumentException;
use Tebru\Gson\Annotation\JsonAdapter;
use Tebru\Gson\Internal\TypeAdapter\CustomWrappedTypeAdapter;
use Tebru\Gson\JsonDeserializer;
use Tebru\Gson\JsonSerializer;
use Tebru\Gson\TypeAdapter;
use Tebru\Gson\TypeAdapterFactory;
use Tebru\PhpType\TypeToken;

/**
 * Class TypeAdapterProvider
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class TypeAdapterProvider
{
    /**
     * A cache of created type adapters
     *
     * @var TypeAdapter[]
     */
    private $typeAdapters = [];

    /**
     * All registered [@see TypeAdapter] factories
     *
     * @var TypeAdapterFactory[]
     */
    private $typeAdapterFactories;

    /**
     * @var ConstructorConstructor
     */
    private $constructorConstructor;

    /**
     * Constructor
     *
     * @param array $typeAdapterFactories
     * @param ConstructorConstructor $constructorConstructor
     */
    public function __construct(array $typeAdapterFactories, ConstructorConstructor $constructorConstructor)
    {
        $this->typeAdapterFactories = $typeAdapterFactories;
        $this->constructorConstructor = $constructorConstructor;
    }

    /**
     * Creates a key based on the type, and optionally the class that should be skipped.
     * Returns the [@see TypeAdapter] if it has already been created, otherwise loops
     * over all of the factories and finds a type adapter that supports the type.
     *
     * @param TypeToken $type
     * @param TypeAdapterFactory|null $skip
     * @return TypeAdapter
     * @throws \InvalidArgumentException if the type cannot be handled by a type adapter
     */
    public function getAdapter(TypeToken $type, TypeAdapterFactory $skip = null): TypeAdapter
    {
        $key = (string)$type;
        if (null === $skip && isset($this->typeAdapters[$key])) {
            return $this->typeAdapters[$key];
        }

        foreach ($this->typeAdapterFactories as $typeAdapterFactory) {
            if ($typeAdapterFactory === $skip) {
                continue;
            }

            if (!$typeAdapterFactory->supports($type)) {
                continue;
            }

            $adapter = $typeAdapterFactory->create($type, $this);

            // do not save skipped adapters
            if (null === $skip) {
                $this->typeAdapters[$key] = $adapter;
            }

            return $adapter;
        }

        throw new InvalidArgumentException(\sprintf(
            'The type "%s" could not be handled by any of the registered type adapters',
            (string)$type
        ));
    }

    /**
     * Get a type adapter from a [@see JsonAdapter] annotation
     *
     * The class may be a TypeAdapter, TypeAdapterFactory, JsonSerializer, or JsonDeserializer
     *
     * @param TypeToken $type
     * @param JsonAdapter $jsonAdapterAnnotation
     * @return TypeAdapter
     * @throws \InvalidArgumentException
     */
    public function getAdapterFromAnnotation(TypeToken $type, JsonAdapter $jsonAdapterAnnotation): TypeAdapter
    {
        $object = $this->constructorConstructor->get(new TypeToken($jsonAdapterAnnotation->getValue()))->construct();

        if ($object instanceof TypeAdapter) {
            return $object;
        }

        if ($object instanceof TypeAdapterFactory) {
            return $object->create($type, $this);
        }

        if ($object instanceof JsonSerializer && $object instanceof JsonDeserializer) {
            return new CustomWrappedTypeAdapter($type, $this, $object, $object);
        }

        if ($object instanceof JsonSerializer) {
            return new CustomWrappedTypeAdapter($type, $this, $object);
        }

        if ($object instanceof JsonDeserializer) {
            return new CustomWrappedTypeAdapter($type, $this, null, $object);
        }

        throw new InvalidArgumentException(\sprintf(
            'The type adapter must be an instance of TypeAdapter, TypeAdapterFactory, JsonSerializer, or JsonDeserializer, but "%s" was found',
            \get_class($object)
        ));
    }
}
