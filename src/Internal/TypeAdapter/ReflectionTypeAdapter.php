<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal\TypeAdapter;

use Tebru\Gson\Internal\Data\Property;
use Tebru\Gson\Internal\Excluder;
use Tebru\Gson\JsonWritable;
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
     * @var Excluder
     */
    private $excluder;

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
     * @param Excluder $excluder
     * @param ObjectConstructor $objectConstructor
     * @param PropertyCollection $properties
     */
    public function __construct(
        Excluder $excluder,
        ObjectConstructor $objectConstructor,
        PropertyCollection $properties
    )
    {
        $this->excluder = $excluder;
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
            if (null === $property || $property->skipDeserialize() || $this->excluder->excludePropertyByStrategy($property, false)) {
                $reader->skipValue();
                continue;
            }

            $property->read($reader, $object);
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
        if (null === $value) {
            $writer->writeNull();

            return;
        }

        $writer->beginObject();

        /** @var Property $property */
        foreach ($this->properties as $property) {
            $writer->name($property->getSerializedName());

            if ($property->skipSerialize() || $this->excluder->excludePropertyByStrategy($property, true)) {
                $writer->writeNull();

                continue;
            }

            $property->write($writer, $value);
        }

        $writer->endObject();
    }
}
