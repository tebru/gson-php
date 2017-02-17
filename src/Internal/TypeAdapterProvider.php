<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal;

use Doctrine\Common\Cache\CacheProvider;
use InvalidArgumentException;
use Tebru\Gson\Annotation\JsonAdapter;
use Tebru\Gson\Internal\TypeAdapter\CustomWrappedTypeAdapter;
use Tebru\Gson\JsonDeserializer;
use Tebru\Gson\JsonSerializer;
use Tebru\Gson\PhpType;
use Tebru\Gson\TypeAdapter;
use Tebru\Gson\TypeAdapterFactory;

/**
 * Class TypeAdapterProvider
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class TypeAdapterProvider
{
    /**
     * A cache of mapped factories
     *
     * @var CacheProvider
     */
    private $typeAdapterCache;

    /**
     * All registered [@see TypeAdapter]s
     *
     * @var TypeAdapterFactory[]
     */
    private $typeAdapterFactories = [];

    /**
     * Constructor
     *
     * @param array $typeAdapterFactories
     * @param CacheProvider $cache
     */
    public function __construct(array $typeAdapterFactories, CacheProvider $cache)
    {
        $this->typeAdapterFactories = $typeAdapterFactories;
        $this->typeAdapterCache = $cache;
    }

    /**
     * Add type adapter directly into cache
     *
     * @param string $type
     * @param TypeAdapter $typeAdapter
     */
    public function addTypeAdapter(string $type, TypeAdapter $typeAdapter): void
    {
        $this->typeAdapterCache->save($type, $typeAdapter);
    }

    /**
     * Creates a key based on the type, and optionally the class that should be skipped.
     * Returns the [@see TypeAdapter] if it has already been created, otherwise loops
     * over all of the factories and finds a type adapter that supports the type.
     *
     * @param PhpType $type
     * @param TypeAdapterFactory $skip
     * @return TypeAdapter
     * @throws \InvalidArgumentException if the type cannot be handled by a type adapter
     */
    public function getAdapter(PhpType $type, TypeAdapterFactory $skip = null): TypeAdapter
    {
        $fullType = (string) $type;
        $typeAdapter = $this->typeAdapterCache->fetch($fullType);
        if (null === $skip && false !== $typeAdapter) {
            return $typeAdapter;
        }

        foreach ($this->typeAdapterFactories as $typeAdapterFactory) {
            if ($typeAdapterFactory === $skip) {
                continue;
            }

            if (!$typeAdapterFactory->supports($type)) {
                continue;
            }

            $adapter = $typeAdapterFactory->create($type, $this);
            $this->typeAdapterCache->save($fullType, $adapter);

            return $adapter;
        }

        throw new InvalidArgumentException(sprintf(
            'The type "%s" could not be handled by any of the registered type adapters',
            (string) $type
        ));
    }

    /**
     * Get a type adapter from a [@see JsonAdapter] annotation
     *
     * The class may be a TypeAdapter, TypeAdapterFactory, JsonSerializer, or JsonDeserializer
     *
     * @param PhpType $phpType
     * @param JsonAdapter $jsonAdapterAnnotation
     * @return TypeAdapter
     * @throws \InvalidArgumentException if an invalid adapter is found
     */
    public function getAdapterFromAnnotation(PhpType $phpType, JsonAdapter $jsonAdapterAnnotation): TypeAdapter
    {
        $class = $jsonAdapterAnnotation->getClass();
        $object = new $class();

        if ($object instanceof TypeAdapter) {
            return $object;
        }

        if ($object instanceof TypeAdapterFactory) {
            return $object->create($phpType, $this);
        }

        if ($object instanceof JsonSerializer && $object instanceof JsonDeserializer) {
            return new CustomWrappedTypeAdapter($phpType, $this, $object, $object);
        }

        if ($object instanceof JsonSerializer) {
            return new CustomWrappedTypeAdapter($phpType, $this, $object);
        }

        if ($object instanceof JsonDeserializer) {
            return new CustomWrappedTypeAdapter($phpType, $this, null, $object);
        }

        throw new InvalidArgumentException(sprintf(
            'The type adapter must be an instance of TypeAdapter, TypeAdapterFactory, JsonSerializer, or JsonDeserializer, but "%s" was found',
            get_class($object)
        ));
    }
}
