<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal\TypeAdapter;

use Tebru\Gson\Annotation\JsonAdapter;
use Tebru\Gson\Annotation\VirtualProperty;
use Tebru\Gson\ClassMetadata;
use Tebru\Gson\Internal\Data\MetadataPropertyCollection;
use Tebru\Gson\Internal\Data\Property;
use Tebru\Gson\Internal\DefaultExclusionData;
use Tebru\Gson\Internal\Excluder;
use Tebru\Gson\Internal\ObjectConstructor\CreateFromInstance;
use Tebru\Gson\Internal\ObjectConstructorAware;
use Tebru\Gson\Internal\ObjectConstructorAwareTrait;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\JsonWritable;
use Tebru\Gson\Internal\Data\PropertyCollection;
use Tebru\Gson\JsonReadable;
use Tebru\Gson\Internal\ObjectConstructor;
use Tebru\Gson\JsonToken;
use Tebru\Gson\TypeAdapter;
use TypeError;

/**
 * Class ReflectionTypeAdapter
 *
 * Uses reflected class properties to read/write object
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class ReflectionTypeAdapter extends TypeAdapter implements ObjectConstructorAware
{
    use ObjectConstructorAwareTrait;

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
     * @var TypeAdapterProvider
     */
    private $typeAdapterProvider;

    /**
     * Constructor
     *
     * @param ObjectConstructor $objectConstructor
     * @param PropertyCollection $properties
     * @param MetadataPropertyCollection $metadataPropertyCollection
     * @param ClassMetadata $classMetadata
     * @param Excluder $excluder
     * @param TypeAdapterProvider $typeAdapterProvider
     */
    public function __construct(
        ObjectConstructor $objectConstructor,
        PropertyCollection $properties,
        MetadataPropertyCollection $metadataPropertyCollection,
        ClassMetadata $classMetadata,
        Excluder $excluder,
        TypeAdapterProvider $typeAdapterProvider
    ) {
        $this->objectConstructor = $objectConstructor;
        $this->properties = $properties;
        $this->metadataPropertyCollection = $metadataPropertyCollection;
        $this->classMetadata = $classMetadata;
        $this->excluder = $excluder;
        $this->typeAdapterProvider = $typeAdapterProvider;
    }
    /**
     * Read the next value, convert it to its type and return it
     *
     * @param JsonReadable $reader
     * @return object
     */
    public function read(JsonReadable $reader)
    {
        if ($reader->peek() === JsonToken::NULL) {
            $reader->nextNull();
            return null;
        }

        $object = $this->objectConstructor->construct();
        $exclusionData = new DefaultExclusionData(false, clone $object, $reader->getPayload());

        if ($this->excluder->excludeClassByStrategy($this->classMetadata, $exclusionData)) {
            $reader->skipValue();

            return null;
        }

        $reader->beginObject();

        $virtualProperty = $this->classMetadata->getAnnotation(VirtualProperty::class);
        if ($virtualProperty !== null) {
            $reader->nextName();
            $reader->beginObject();
        }

        while ($reader->hasNext()) {
            $name = $reader->nextName();
            $property = $this->properties->getBySerializedName($name);
            if (
                null === $property
                || $property->skipDeserialize()
                || $this->excluder->excludePropertyByStrategy($this->metadataPropertyCollection->get($property->getRealName()), $exclusionData)
            ) {
                $reader->skipValue();
                continue;
            }

            /** @var JsonAdapter $jsonAdapterAnnotation */
            $jsonAdapterAnnotation = $property->getAnnotations()->get(JsonAdapter::class);
            $adapter = null === $jsonAdapterAnnotation
                ? $this->typeAdapterProvider->getAdapter($property->getType())
                : $this->typeAdapterProvider->getAdapterFromAnnotation($property->getType(), $jsonAdapterAnnotation);

            if ($adapter instanceof ObjectConstructorAware) {
                $nestedObject = null;
                try {
                    $nestedObject = $property->get($object);
                } /** @noinspection BadExceptionsProcessingInspection */ catch (TypeError $error) {
                    // this may occur when attempting to get a nested object that doesn't exist and
                    // the method return is not nullable. The type error only occurs because we are
                    // may be calling the getter before data exists.
                }

                if ($nestedObject !== null) {
                    $adapter->setObjectConstructor(new CreateFromInstance($nestedObject));
                }
            }

            $property->set($object, $adapter->read($reader));
        }
        $reader->endObject();

        if ($virtualProperty !== null) {
            $reader->endObject();
        }

        return $object;
    }

    /**
     * Write the value to the writer for the type
     *
     * @param JsonWritable $writer
     * @param object $value
     * @return void
     */
    public function write(JsonWritable $writer, $value): void
    {
        if (null === $value) {
            $writer->writeNull();

            return;
        }

        $exclusionData = new DefaultExclusionData(true, $value);

        if ($this->excluder->excludeClassByStrategy($this->classMetadata, $exclusionData)) {
            $writer->writeNull();

            return;
        }

        $writer->beginObject();

        $virtualProperty = $this->classMetadata->getAnnotation(VirtualProperty::class) ;
        if ($virtualProperty !== null) {
            $writer->name($virtualProperty->getValue());
            $writer->beginObject();
        }

        /** @var Property $property */
        foreach ($this->properties as $property) {
            $writer->name($property->getSerializedName());

            if (
                $property->skipSerialize()
                || $this->excluder->excludePropertyByStrategy($this->metadataPropertyCollection->get($property->getRealName()), $exclusionData)
            ) {
                $writer->writeNull();

                continue;
            }

            /** @var JsonAdapter $jsonAdapterAnnotation */
            $jsonAdapterAnnotation = $property->getAnnotations()->get(JsonAdapter::class);
            $adapter = null === $jsonAdapterAnnotation
                ? $this->typeAdapterProvider->getAdapter($property->getType())
                : $this->typeAdapterProvider->getAdapterFromAnnotation($property->getType(), $jsonAdapterAnnotation);
            $adapter->write($writer, $property->get($value));
        }

        $writer->endObject();

        if ($virtualProperty !== null) {
            $writer->endObject();
        }
    }
}
