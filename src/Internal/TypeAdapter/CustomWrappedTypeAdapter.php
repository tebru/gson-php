<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal\TypeAdapter;

use Tebru\Gson\Internal\DefaultJsonDeserializationContext;
use Tebru\Gson\Internal\JsonWritable;
use Tebru\Gson\Internal\PhpType;
use Tebru\Gson\Internal\TypeAdapter\Factory\CustomWrappedTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\JsonDeserializer;
use Tebru\Gson\JsonReadable;
use Tebru\Gson\JsonSerializer;
use Tebru\Gson\TypeAdapter;

/**
 * Class CustomWrappedTypeAdapter
 *
 * Wraps a [@see JsonSerializer] or [@see JsonDeserializer] and delegates if either is null
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class CustomWrappedTypeAdapter extends TypeAdapter
{
    /**
     * @var PhpType
     */
    private $phpType;

    /**
     * @var TypeAdapterProvider
     */
    private $typeAdapterProvider;

    /**
     * @var JsonSerializer
     */
    private $serializer;

    /**
     * @var JsonDeserializer
     */
    private $deserializer;

    /**
     * Constructor
     *
     * @param JsonDeserializer $deserializer
     * @param JsonSerializer $serializer
     * @param PhpType $phpType
     * @param TypeAdapterProvider $typeAdapterProvider
     */
    public function __construct(
        PhpType $phpType,
        TypeAdapterProvider $typeAdapterProvider,
        JsonSerializer $serializer = null,
        JsonDeserializer $deserializer = null
    ) {
        $this->phpType = $phpType;
        $this->typeAdapterProvider = $typeAdapterProvider;
        $this->serializer = $serializer;
        $this->deserializer = $deserializer;
    }

    /**
     * Read the next value, convert it to its type and return it
     *
     * @param JsonReadable $reader
     * @return mixed
     * @throws \InvalidArgumentException if the type cannot be handled by a type adapter
     * @throws \LogicException If the token can not be handled
     */
    public function read(JsonReadable $reader)
    {
        if (null === $this->deserializer) {
            $adapter = $this->typeAdapterProvider->getAdapter($this->phpType, CustomWrappedTypeAdapterFactory::class);

            return $adapter->read($reader);
        }

        $jsonElementTypeAdapter = new JsonElementTypeAdapter();
        $jsonElement = $jsonElementTypeAdapter->read($reader);

        return $this->deserializer->deserialize(
            $jsonElement,
            $this->phpType,
            new DefaultJsonDeserializationContext($this->typeAdapterProvider, $this->phpType)
        );
    }

    /**
     * Write the value to the writer for the type
     *
     * @param JsonWritable $writer
     * @param mixed $value
     * @return void
     */
    public function write(JsonWritable $writer, $value): void
    {
    }
}
