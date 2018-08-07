<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal\TypeAdapter;

use Tebru\Gson\Annotation\JsonAdapter;
use Tebru\Gson\ClassMetadata;
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
     * @var null|string
     */
    private $classVirtualProperty;

    /**
     * @var bool
     */
    private $skipSerialize;

    /**
     * @var bool
     */
    private $skipDeserialize;

    /**
     * @var bool
     */
    private $hasSerializationStrategies;

    /**
     * @var bool
     */
    private $hasDeserializationStrategies;

    /**
     * An memory cache of used type adapters
     *
     * @var TypeAdapter[]
     */
    private $adapters = [];

    /**
     * A memory cache of read properties
     *
     * @var Property[]
     */
    private $propertyCache = [];

    /**
     * Constructor
     *
     * @param ObjectConstructor $objectConstructor
     * @param PropertyCollection $properties
     * @param ClassMetadata $classMetadata
     * @param Excluder $excluder
     * @param TypeAdapterProvider $typeAdapterProvider
     * @param null|string $classVirtualProperty
     * @param bool $skipSerialize
     * @param bool $skipDeserialize
     */
    public function __construct(
        ObjectConstructor $objectConstructor,
        PropertyCollection $properties,
        ClassMetadata $classMetadata,
        Excluder $excluder,
        TypeAdapterProvider $typeAdapterProvider,
        ?string $classVirtualProperty,
        bool $skipSerialize,
        bool $skipDeserialize
    ) {
        $this->objectConstructor = $objectConstructor;
        $this->properties = $properties;
        $this->classMetadata = $classMetadata;
        $this->excluder = $excluder;
        $this->typeAdapterProvider = $typeAdapterProvider;
        $this->classVirtualProperty = $classVirtualProperty;
        $this->skipSerialize = $skipSerialize;
        $this->skipDeserialize = $skipDeserialize;

        $this->hasSerializationStrategies = $this->excluder->hasSerializationStrategies();
        $this->hasDeserializationStrategies = $this->excluder->hasDeserializationStrategies();
    }
    /**
     * Read the next value, convert it to its type and return it
     *
     * @param JsonReadable $reader
     * @return object
     */
    public function read(JsonReadable $reader)
    {
        if ($this->skipDeserialize) {
            $reader->skipValue();
            return null;
        }

        if ($reader->peek() === JsonToken::NULL) {
            $reader->nextNull();
            return null;
        }

        $object = $this->objectConstructor->construct();
        $exclusionData = $this->hasDeserializationStrategies
            ? new DefaultExclusionData(false, clone $object, $reader->getPayload())
            : null;

        if ($exclusionData && $this->excluder->excludeClassByDeserializationStrategy($this->classMetadata, $exclusionData)) {
            $reader->skipValue();

            return null;
        }

        $reader->beginObject();

        if ($this->classVirtualProperty !== null) {
            $reader->nextName();
            $reader->beginObject();
        }

        $usesExisting = $reader->getContext()->usesExistingObject();

        while ($reader->hasNext()) {
            $name = $reader->nextName();
            $property = $this->propertyCache[$name] ?? $this->propertyCache[$name] = $this->properties->getBySerializedName($name);

            if ($property === null) {
                $reader->skipValue();
                continue;
            }

            $realName = $property->getName();

            if (
                $property->skipDeserialize()
                || (
                    $exclusionData
                    && $this->excluder->excludePropertyByDeserializationStrategy($property, $exclusionData)
                )
            ) {
                $reader->skipValue();
                continue;
            }

            $adapter = $this->adapters[$realName] ?? null;
            if ($adapter === null) {
                /** @var JsonAdapter $jsonAdapterAnnotation */
                $jsonAdapterAnnotation = $property->getAnnotations()->get(JsonAdapter::class);
                $adapter = null === $jsonAdapterAnnotation
                    ? $this->typeAdapterProvider->getAdapter($property->getType())
                    : $this->typeAdapterProvider->getAdapterFromAnnotation(
                        $property->getType(),
                        $jsonAdapterAnnotation
                    );
                $this->adapters[$realName] = $adapter;
            }

            if ($adapter instanceof ObjectConstructorAware && $usesExisting) {
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

        if ($this->classVirtualProperty !== null) {
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
        if ($this->skipSerialize || $value === null) {
            $writer->writeNull();
            return;
        }

        $exclusionData = $this->hasSerializationStrategies
            ? new DefaultExclusionData(true, $value)
            : null;

        if (
            $exclusionData
            && $this->excluder->excludeClassBySerializationStrategy($this->classMetadata, $exclusionData)
        ) {
            $writer->writeNull();
            return;
        }

        $writer->beginObject();

        if ($this->classVirtualProperty !== null) {
            $writer->name($this->classVirtualProperty);
            $writer->beginObject();
        }

        /** @var Property $property */
        foreach ($this->properties as $property) {
            $realName = $property->getName();
            $writer->name($property->getSerializedName());

            if (
                $property->skipSerialize()
                || (
                    $exclusionData
                    && $this->excluder->excludePropertyBySerializationStrategy($property, $exclusionData)
                )
            ) {
                $writer->writeNull();

                continue;
            }

            $adapter = $this->adapters[$realName] ?? null;
            if (!isset($this->adapters[$realName])) {
                /** @var JsonAdapter $jsonAdapterAnnotation */
                $jsonAdapterAnnotation = $property->getAnnotations()->get(JsonAdapter::class);
                $adapter = null === $jsonAdapterAnnotation
                    ? $this->typeAdapterProvider->getAdapter($property->getType())
                    : $this->typeAdapterProvider->getAdapterFromAnnotation($property->getType(), $jsonAdapterAnnotation);
                $this->adapters[$realName] = $adapter;
            }
            $adapter->write($writer, $property->get($value));
        }

        $writer->endObject();

        if ($this->classVirtualProperty !== null) {
            $writer->endObject();
        }
    }
}
