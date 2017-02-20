<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal\TypeAdapter;

use Tebru\Gson\ClassMetadata;
use Tebru\Gson\Internal\Data\MetadataPropertyCollection;
use Tebru\Gson\Internal\Data\Property;
use Tebru\Gson\Internal\Excluder;
use Tebru\Gson\JsonWritable;
use Tebru\Gson\Internal\Data\PropertyCollection;
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
     * @var ObjectConstructor
     */
    private $objectConstructor;

    /**
     * @var PropertyCollection
     */
    private $properties;

    /**
     * @var MetadataPropertyCollection
     */
    private $metadataPropertyCollection;

    /**
     * @var ClassMetadata
     */
    private $classMetadata;

    /**
     * @var Excluder
     */
    private $excluder;

    /**
     * Constructor
     *
     * @param ObjectConstructor $objectConstructor
     * @param PropertyCollection $properties
     * @param MetadataPropertyCollection $metadataPropertyCollection
     * @param ClassMetadata $classMetadata
     * @param Excluder $excluder
     */
    public function __construct(
        ObjectConstructor $objectConstructor,
        PropertyCollection $properties,
        MetadataPropertyCollection $metadataPropertyCollection,
        ClassMetadata $classMetadata,
        Excluder $excluder
    ) {
        $this->objectConstructor = $objectConstructor;
        $this->properties = $properties;
        $this->metadataPropertyCollection = $metadataPropertyCollection;
        $this->classMetadata = $classMetadata;
        $this->excluder = $excluder;
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

        if ($this->excluder->excludeClassByStrategy($this->classMetadata, false)) {
            $reader->skipValue();

            return null;
        }

        $object = $this->objectConstructor->construct();

        $reader->beginObject();
        while ($reader->hasNext()) {
            $name = $reader->nextName();
            $property = $this->properties->getBySerializedName($name);
            if (
                null === $property
                || $property->skipDeserialize()
                || $this->excluder->excludePropertyByStrategy($this->metadataPropertyCollection->get($property->getRealName()), false)
            ) {
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

        if ($this->excluder->excludeClassByStrategy($this->classMetadata, true)) {
            $writer->writeNull();

            return;
        }

        $writer->beginObject();

        /** @var Property $property */
        foreach ($this->properties as $property) {
            $writer->name($property->getSerializedName());

            if (
                $property->skipSerialize()
                || $this->excluder->excludePropertyByStrategy($this->metadataPropertyCollection->get($property->getRealName()), true)
            ) {
                $writer->writeNull();

                continue;
            }

            $property->write($writer, $value);
        }

        $writer->endObject();
    }
}
