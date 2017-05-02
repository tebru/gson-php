<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal\TypeAdapter;

use Tebru\Gson\Annotation\JsonAdapter;
use Tebru\Gson\ClassMetadata;
use Tebru\Gson\Internal\Data\AnnotationSet;
use Tebru\Gson\Internal\Data\MetadataPropertyCollection;
use Tebru\Gson\Internal\Data\Property;
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
     * @return mixed
     * @throws \InvalidArgumentException
     * @throws \Tebru\PhpType\Exception\MalformedTypeException If the type cannot be parsed
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

            /** @var JsonAdapter $jsonAdapterAnnotation */
            $jsonAdapterAnnotation = $property->getAnnotations()->getAnnotation(JsonAdapter::class, AnnotationSet::TYPE_PROPERTY);
            $adapter = null === $jsonAdapterAnnotation
                ? $this->typeAdapterProvider->getAdapter($property->getType())
                : $this->typeAdapterProvider->getAdapterFromAnnotation($property->getType(), $jsonAdapterAnnotation);

            if ($adapter instanceof ObjectConstructorAware) {
                $nestedObject = $property->get($object);
                if ($nestedObject !== null) {
                    $adapter->setObjectConstructor(new CreateFromInstance($nestedObject));
                }
            }

            $property->set($object, $adapter->read($reader));
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
     * @throws \InvalidArgumentException
     * @throws \Tebru\PhpType\Exception\MalformedTypeException If the type cannot be parsed
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

            /** @var JsonAdapter $jsonAdapterAnnotation */
            $jsonAdapterAnnotation = $property->getAnnotations()->getAnnotation(JsonAdapter::class, AnnotationSet::TYPE_PROPERTY);
            $adapter = null === $jsonAdapterAnnotation
                ? $this->typeAdapterProvider->getAdapter($property->getType())
                : $this->typeAdapterProvider->getAdapterFromAnnotation($property->getType(), $jsonAdapterAnnotation);
            $adapter->write($writer, $property->get($value));
        }

        $writer->endObject();
    }
}
