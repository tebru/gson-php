<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson;

use Tebru\Gson\Element\JsonElement;
use Tebru\Gson\Internal\DefaultReaderContext;
use Tebru\Gson\Internal\JsonDecodeReader;
use Tebru\Gson\Internal\JsonElementReader;
use Tebru\Gson\Internal\JsonElementWriter;
use Tebru\Gson\Internal\JsonEncodeWriter;

/**
 * Class TypeAdapter
 *
 * Create custom TypeAdapters by extending this class.  This provides a low level
 * alternative to creating JsonSerializers or JsonDeserializers.  The advantage of
 * a TypeAdapter is you do not have to pay for converting to and from JsonElement
 * objects.
 *
 * @author Nate Brunette <n@tebru.net>
 */
abstract class TypeAdapter
{
    /**
     * Read the next value, convert it to its type and return it
     *
     * @param JsonReadable $reader
     * @return mixed
     */
    abstract public function read(JsonReadable $reader);

    /**
     * Write the value to the writer for the type
     *
     * @param JsonWritable $writer
     * @param mixed $value
     * @return void
     */
    abstract public function write(JsonWritable $writer, $value): void;

    /**
     * Constructs a JsonReader for a given string of json and passes it to ::read()
     *
     * @param string $json
     * @param ReaderContext|null $context
     * @return mixed
     */
    public function readFromJson($json, ?ReaderContext $context = null)
    {
        return $this->read(new JsonDecodeReader($json, $context ?? new DefaultReaderContext()));
    }

    /**
     * Constructs a JsonElementReader with a JsonElement and passes it to ::read()
     *
     * @param JsonElement $jsonElement
     * @param null|ReaderContext $context
     * @return mixed
     */
    public function readFromJsonElement(JsonElement $jsonElement, ?ReaderContext $context = null)
    {
        return $this->read(new JsonElementReader($jsonElement, $context ?? new DefaultReaderContext()));
    }

    /**
     * Constructs a JsonWriter and passes it to ::write().  Returns the written json.
     *
     * @param mixed $var
     * @param bool $serializeNull
     * @return string
     */
    public function writeToJson($var, bool $serializeNull = false): string
    {
        $writer = new JsonEncodeWriter();
        $writer->setSerializeNull($serializeNull);

        $this->write($writer, $var);

        return (string) $writer;
    }

    /**
     * Constructs a JsonElementWriter and passes it to ::write().  Returns the JsonElement written.
     *
     * @param mixed $var
     * @param bool $serializeNull
     * @return JsonElement
     */
    public function writeToJsonElement($var, bool $serializeNull = false): JsonElement
    {
        $writer = new JsonElementWriter();
        $writer->setSerializeNull($serializeNull);
        $this->write($writer, $var);

        return $writer->toJsonElement();
    }
}
