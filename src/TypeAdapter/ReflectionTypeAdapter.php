<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\TypeAdapter;

use Tebru\AnnotationReader\AnnotationCollection;
use Tebru\Gson\Annotation\ExclusionCheck;
use Tebru\Gson\Annotation\JsonAdapter;
use Tebru\Gson\Internal\Data\Property;
use Tebru\Gson\Internal\Data\PropertyCollection;
use Tebru\Gson\Internal\DefaultClassMetadata;
use Tebru\Gson\Internal\DefaultDeserializationExclusionData;
use Tebru\Gson\Internal\DefaultSerializationExclusionData;
use Tebru\Gson\Internal\Excluder;
use Tebru\Gson\Internal\ObjectConstructor;
use Tebru\Gson\Internal\ObjectConstructor\CreateFromInstance;
use Tebru\Gson\Internal\ObjectConstructorAware;
use Tebru\Gson\Internal\ObjectConstructorAwareTrait;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\JsonReadable;
use Tebru\Gson\JsonToken;
use Tebru\Gson\JsonWritable;
use Tebru\Gson\TypeAdapter;
use TypeError;

/**
 * Class ReflectionTypeAdapter
 *
 * Uses reflected class properties to read/write object
 *
 * @author Nate Brunette <n@tebru.net>
 */
class ReflectionTypeAdapter extends TypeAdapter implements ObjectConstructorAware
{
    use ObjectConstructorAwareTrait;

    /**
     * @var PropertyCollection
     */
    private $properties;

    /**
     * @var DefaultClassMetadata
     */
    private $classMetadata;

    /**
     * @var AnnotationCollection
     */
    private $classAnnotations;

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
    private $hasClassSerializationStrategies;

    /**
     * @var bool
     */
    private $hasPropertySerializationStrategies;

    /**
     * @var bool
     */
    private $hasClassDeserializationStrategies;

    /**
     * @var bool
     */
    private $hasPropertyDeserializationStrategies;

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
     * @var bool
     */
    private $requireExclusionCheck;

    /**
     * @var bool
     */
    private $hasPropertyExclusionCheck;

    /**
     * Constructor
     *
     * @param ObjectConstructor $objectConstructor
     * @param DefaultClassMetadata $classMetadata
     * @param Excluder $excluder
     * @param TypeAdapterProvider $typeAdapterProvider
     * @param null|string $classVirtualProperty
     * @param bool $requireExclusionCheck
     * @param bool $hasPropertyExclusionCheck
     */
    public function __construct(
        ObjectConstructor $objectConstructor,
        DefaultClassMetadata $classMetadata,
        Excluder $excluder,
        TypeAdapterProvider $typeAdapterProvider,
        ?string $classVirtualProperty,
        bool $requireExclusionCheck,
        bool $hasPropertyExclusionCheck
    ) {
        $this->objectConstructor = $objectConstructor;
        $this->classMetadata = $classMetadata;
        $this->excluder = $excluder;
        $this->typeAdapterProvider = $typeAdapterProvider;
        $this->classVirtualProperty = $classVirtualProperty;
        $this->requireExclusionCheck = $requireExclusionCheck;
        $this->hasPropertyExclusionCheck = $hasPropertyExclusionCheck;

        $this->classAnnotations = $classMetadata->annotations;
        $this->properties = $classMetadata->properties;
        $this->skipSerialize = $classMetadata->skipSerialize;
        $this->skipDeserialize = $classMetadata->skipDeserialize;
        $this->hasClassSerializationStrategies = $this->excluder->hasClassSerializationStrategies();
        $this->hasPropertySerializationStrategies = $this->excluder->hasPropertySerializationStrategies();
        $this->hasClassDeserializationStrategies = $this->excluder->hasClassDeserializationStrategies();
        $this->hasPropertyDeserializationStrategies = $this->excluder->hasPropertyDeserializationStrategies();
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
        $classExclusionCheck = $this->hasClassDeserializationStrategies
            && (!$this->requireExclusionCheck || ($this->requireExclusionCheck && $this->classAnnotations->get(ExclusionCheck::class) !== null));
        $propertyExclusionCheck = $this->hasPropertyDeserializationStrategies
            && (!$this->requireExclusionCheck || ($this->requireExclusionCheck && $this->hasPropertyExclusionCheck));
        $exclusionData = $classExclusionCheck || $propertyExclusionCheck
            ? new DefaultDeserializationExclusionData(clone $object, $reader)
            : null;

        if ($classExclusionCheck && $exclusionData) {
            $this->excluder->applyClassDeserializationExclusionData($exclusionData);

            if ($this->excluder->excludeClassByDeserializationStrategy($this->classMetadata)) {
                $reader->skipValue();
                return null;
            }
        }

        if ($propertyExclusionCheck && $exclusionData) {
            $this->excluder->applyPropertyDeserializationExclusionData($exclusionData);
        }

        $reader->beginObject();

        if ($this->classVirtualProperty !== null) {
            $reader->nextName();
            $reader->beginObject();
        }

        $usesExisting = $reader->getContext()->usesExistingObject();

        while ($reader->hasNext()) {
            $name = $reader->nextName();
            $property = $this->propertyCache[$name] ?? ($this->propertyCache[$name] = ($this->properties->elements[$name] ?? null));

            if ($property === null || $property->skipDeserialize) {
                $reader->skipValue();
                continue;
            }

            $checkProperty = $this->hasPropertyDeserializationStrategies
                && (!$this->requireExclusionCheck || ($this->requireExclusionCheck && $property->annotations->get(ExclusionCheck::class) !== null));
            if ($checkProperty && $this->excluder->excludePropertyByDeserializationStrategy($property)) {
                $reader->skipValue();
                continue;
            }

            $adapter = $this->adapters[$property->realName] ?? null;
            if ($adapter === null) {
                $adapter = $this->getAdapter($property);
            }

            if ($adapter instanceof ObjectConstructorAware && $usesExisting) {
                try {
                    $nestedObject = $property->getterStrategy->get($object);
                } /** @noinspection BadExceptionsProcessingInspection */ catch (TypeError $error) {
                    // this may occur when attempting to get a nested object that doesn't exist and
                    // the method return is not nullable. The type error only occurs because we are
                    // may be calling the getter before data exists.
                    $nestedObject = null;
                }

                if ($nestedObject !== null) {
                    $adapter->setObjectConstructor(new CreateFromInstance($nestedObject));
                }
            }

            $property->setterStrategy->set($object, $adapter->read($reader));
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

        $classExclusionCheck = $this->hasClassSerializationStrategies
            && (!$this->requireExclusionCheck || ($this->requireExclusionCheck && $this->classAnnotations->get(ExclusionCheck::class) !== null));
        $propertyExclusionCheck = $this->hasPropertySerializationStrategies
            && (!$this->requireExclusionCheck || ($this->requireExclusionCheck && $this->hasPropertyExclusionCheck));
        $exclusionData = $classExclusionCheck || $propertyExclusionCheck
            ? new DefaultSerializationExclusionData($value, $writer)
            : null;

        if ($classExclusionCheck && $exclusionData) {
            $this->excluder->applyClassSerializationExclusionData($exclusionData);

            if ($this->excluder->excludeClassBySerializationStrategy($this->classMetadata)) {
                $writer->writeNull();
                return;
            }
        }

        if ($propertyExclusionCheck && $exclusionData) {
            $this->excluder->applyPropertySerializationExclusionData($exclusionData);
        }

        $writer->beginObject();

        if ($this->classVirtualProperty !== null) {
            $writer->name($this->classVirtualProperty);
            $writer->beginObject();
        }

        /** @var Property $property */
        foreach ($this->properties as $property) {
            $writer->name($property->getSerializedName());
            if ($property->skipSerialize) {
                $writer->writeNull();
                continue;
            }

            $checkProperty = $this->hasPropertySerializationStrategies
                && (!$this->requireExclusionCheck || ($this->requireExclusionCheck && $property->annotations->get(ExclusionCheck::class) !== null));
            if ($checkProperty && $this->excluder->excludePropertyBySerializationStrategy($property)) {
                $writer->writeNull();
                continue;
            }

            $adapter = $this->adapters[$property->realName] ?? null;
            if ($adapter === null) {
                $adapter = $this->getAdapter($property);
            }

            $adapter->write($writer, $property->getterStrategy->get($value));
        }

        $writer->endObject();

        if ($this->classVirtualProperty !== null) {
            $writer->endObject();
        }
    }

    /**
     * Get the next type adapter
     *
     * @param Property $property
     * @return TypeAdapter
     */
    private function getAdapter(Property $property): TypeAdapter
    {
        /** @var JsonAdapter $jsonAdapterAnnotation */
        $jsonAdapterAnnotation = $property->annotations->get(JsonAdapter::class);
        $adapter = null === $jsonAdapterAnnotation
            ? $this->typeAdapterProvider->getAdapter($property->type)
            : $this->typeAdapterProvider->getAdapterFromAnnotation($property->type, $jsonAdapterAnnotation);
        $this->adapters[$property->realName] = $adapter;

        return $adapter;
    }
}
