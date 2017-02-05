<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal\TypeAdapter;

use Tebru\Gson\Internal\JsonWritable;
use Tebru\Gson\Internal\Data\PropertyCollection;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\JsonReadable;
use Tebru\Gson\Internal\ObjectConstructor;
use Tebru\Gson\JsonToken;
use Tebru\Gson\TypeAdapter;

/**
 * Class ReflectionTypeAdapter
 *
 * Uses reflected class properties to read/write object
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class ReflectionTypeAdapter extends TypeAdapter
{
    /**
     * @var TypeAdapterProvider
     */
    private $typeAdapterProvider;

    /**
     * @var ObjectConstructor
     */
    private $objectConstructor;

    /**
     * @var PropertyCollection
     */
    private $properties;

    /**
     * Constructor
     *
     * @param TypeAdapterProvider $typeAdapterProvider
     * @param ObjectConstructor $objectConstructor
     * @param PropertyCollection $properties
     */
    public function __construct(TypeAdapterProvider $typeAdapterProvider, ObjectConstructor $objectConstructor, PropertyCollection $properties)
    {
        $this->typeAdapterProvider = $typeAdapterProvider;
        $this->objectConstructor = $objectConstructor;
        $this->properties = $properties;
    }
    /**
     * Read the next value, convert it to its type and return it
     *
     * @param JsonReadable $reader
     * @return mixed
     * @throws \InvalidArgumentException if the type cannot be handled by a type adapter
     */
    public function read(JsonReadable $reader)
    {
        if ($reader->peek() === JsonToken::NULL) {
            return $reader->nextNull();
        }

        $object = $this->objectConstructor->construct();

        $reader->beginObject();
        while ($reader->hasNext()) {
            $name = $reader->nextName();
            $property = $this->properties->getBySerializedName($name);
            if (null === $property || $property->skipDeserialize()) {
                $reader->skipValue();
                continue;
            }

            $adapter = $this->typeAdapterProvider->getAdapter($property->getType());

            $value = $adapter->read($reader);
            if (null === $value) {
                continue;
            }

            $property->set($object, $value);
        }
        $reader->endObject();

        return $object;
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
