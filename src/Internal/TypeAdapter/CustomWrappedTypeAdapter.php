<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal\TypeAdapter;

use Tebru\Gson\Internal\DefaultJsonDeserializationContext;
use Tebru\Gson\Internal\DefaultJsonSerializationContext;
use Tebru\Gson\JsonWritable;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\JsonDeserializer;
use Tebru\Gson\JsonReadable;
use Tebru\Gson\JsonSerializer;
use Tebru\Gson\PhpType;
use Tebru\Gson\TypeAdapter;
use Tebru\Gson\TypeAdapterFactory;

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
    private $type;

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
     * @var TypeAdapterFactory
     */
    private $skip;

    /**
     * Constructor
     *
     * @param PhpType $type
     * @param TypeAdapterProvider $typeAdapterProvider
     * @param JsonSerializer $serializer
     * @param JsonDeserializer $deserializer
     * @param TypeAdapterFactory $skip
     */
    public function __construct(
        PhpType $type,
        TypeAdapterProvider $typeAdapterProvider,
        JsonSerializer $serializer = null,
        JsonDeserializer $deserializer = null,
        TypeAdapterFactory $skip = null
    ) {
        $this->type = $type;
        $this->typeAdapterProvider = $typeAdapterProvider;
        $this->serializer = $serializer;
        $this->deserializer = $deserializer;
        $this->skip = $skip;
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
            $adapter = $this->typeAdapterProvider->getAdapter($this->type, $this->skip);

            return $adapter->read($reader);
        }

        $jsonElementTypeAdapter = new JsonElementTypeAdapter();
        $jsonElement = $jsonElementTypeAdapter->read($reader);

        return $this->deserializer->deserialize(
            $jsonElement,
            $this->type,
            new DefaultJsonDeserializationContext($this->typeAdapterProvider)
        );
    }

    /**
     * Write the value to the writer for the type
     *
     * @param JsonWritable $writer
     * @param mixed $value
     * @return void
     * @throws \InvalidArgumentException if the type cannot be handled by a type adapter
     * @throws \LogicException If the token can not be handled
     * @throws \Tebru\Gson\Exception\UnsupportedMethodException
     */
    public function write(JsonWritable $writer, $value): void
    {
        if (null === $this->serializer) {
            $adapter = $this->typeAdapterProvider->getAdapter($this->type, $this->skip);
            $adapter->write($writer, $value);

            return;
        }

        if (null === $value) {
            $writer->writeNull();

            return;
        }

        $jsonElement = $this->serializer->serialize(
            $value,
            $this->type,
            new DefaultJsonSerializationContext($this->typeAdapterProvider)
        );

        $jsonElementTypeAdapter = new JsonElementTypeAdapter();
        $jsonElementTypeAdapter->write($writer, $jsonElement);
    }
}
