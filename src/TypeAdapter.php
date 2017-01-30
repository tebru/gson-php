<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson;

use GuzzleHttp\Psr7;
use Tebru\Gson\Element\JsonElement;
use Tebru\Gson\Internal\JsonElementReader;
use Tebru\Gson\Internal\JsonReader;
use Tebru\Gson\Internal\JsonWritable;

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
    abstract public function write(JsonWritable $writer, $value);

    /**
     * Constructs a JsonReader for a given string of json and passes it to ::read()
     *
     * @param string|resource $json
     * @return object
     */
    public function readFromJson($json)
    {
        $stream = Psr7\stream_for($json);

        return $this->read(new JsonReader($stream));
    }

    /**
     * Constructs a JsonElementReader with a JsonElement and passes it to ::read()
     *
     * @param JsonElement $jsonElement
     * @return object
     */
    public function readFromJsonElement(JsonElement $jsonElement)
    {
        return $this->read(new JsonElementReader($jsonElement));
    }

    /**
     * Constructs a JsonWriter and passes it to ::write().  Returns the written json.
     *
     * @param object $object
     * @return string
     */
    public function writeToJson($object)
    {
    }

    /**
     * Constructs a JsonElementWriter and passes it to ::write().  Returns the JsonElement written.
     *
     * @param object $object
     * @return JsonElement
     */
    public function writeToJsonElement($object)
    {
    }
}
