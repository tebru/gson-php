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
use Tebru\Gson\Context\ReaderContext;
use Tebru\Gson\Context\WriterContext;
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
    protected $properties;

    /**
     * @var DefaultClassMetadata
     */
    protected $classMetadata;

    /**
     * @var AnnotationCollection
     */
    protected $classAnnotations;

    /**
     * @var Excluder
     */
    protected $excluder;

    /**
     * @var TypeAdapterProvider
     */
    protected $typeAdapterProvider;

    /**
     * @var null|string
     */
    protected $classVirtualProperty;

    /**
     * @var bool
     */
    protected $skipSerialize;

    /**
     * @var bool
     */
    protected $skipDeserialize;

    /**
     * @var bool
     */
    protected $hasClassSerializationStrategies;

    /**
     * @var bool
     */
    protected $hasPropertySerializationStrategies;

    /**
     * @var bool
     */
    protected $hasClassDeserializationStrategies;

    /**
     * @var bool
     */
    protected $hasPropertyDeserializationStrategies;

    /**
     * An memory cache of used type adapters
     *
     * @var TypeAdapter[]
     */
    protected $adapters = [];

    /**
     * A memory cache of read properties
     *
     * @var Property[]
     */
    protected $propertyCache = [];

    /**
     * @var bool
     */
    protected $requireExclusionCheck;

    /**
     * @var bool
     */
    protected $hasPropertyExclusionCheck;

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
     * @param array $value
     * @param ReaderContext $context
     * @return object
     */
    public function read($value, ReaderContext $context)
    {
        if ($this->skipDeserialize || $value === null) {
            return null;
        }

        $object = $this->objectConstructor->construct();
        $classExclusionCheck = $this->hasClassDeserializationStrategies
            && (!$this->requireExclusionCheck || ($this->requireExclusionCheck && $this->classAnnotations->get(ExclusionCheck::class) !== null));
        $propertyExclusionCheck = $this->hasPropertyDeserializationStrategies
            && (!$this->requireExclusionCheck || ($this->requireExclusionCheck && $this->hasPropertyExclusionCheck));
        $exclusionData = $classExclusionCheck || $propertyExclusionCheck
            ? new DefaultDeserializationExclusionData(clone $object, $context)
            : null;

        if ($classExclusionCheck && $exclusionData) {
            $this->excluder->applyClassDeserializationExclusionData($exclusionData);

            if ($this->excluder->excludeClassByDeserializationStrategy($this->classMetadata)) {
                return null;
            }
        }

        if ($propertyExclusionCheck && $exclusionData) {
            $this->excluder->applyPropertyDeserializationExclusionData($exclusionData);
        }

        if ($this->classVirtualProperty !== null) {
            $value = array_shift($value);
        }

        $usesExisting = $context->usesExistingObject();
        $enableScalarAdapters = $context->enableScalarAdapters();

        foreach ($value as $name => $item) {
            $property = $this->propertyCache[$name] ?? ($this->propertyCache[$name] = ($this->properties->elements[$name] ?? null));

            if ($property === null || $property->skipDeserialize) {
                continue;
            }

            $checkProperty = $this->hasPropertyDeserializationStrategies
                && (!$this->requireExclusionCheck || ($this->requireExclusionCheck && $property->annotations->get(ExclusionCheck::class) !== null));
            if ($checkProperty && $this->excluder->excludePropertyByDeserializationStrategy($property)) {
                continue;
            }

            if (!$enableScalarAdapters && $property->isScalar) {
                $property->setterStrategy->set($object, $item);
                continue;
            }

            $adapter = $this->adapters[$name] ?? $this->getAdapter($property);
            if ($usesExisting && $adapter instanceof ObjectConstructorAware) {
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

            $property->setterStrategy->set($object, $adapter->read($item, $context));
        }

        return $object;
    }

    /**
     * Write the value to the writer for the type
     *
     * @param object $value
     * @param WriterContext $context
     * @return array|null
     */
    public function write($value, WriterContext $context): ?array
    {
        if ($this->skipSerialize || $value === null) {
            return null;
        }

        $classExclusionCheck = $this->hasClassSerializationStrategies
            && (!$this->requireExclusionCheck || ($this->requireExclusionCheck && $this->classAnnotations->get(ExclusionCheck::class) !== null));
        $propertyExclusionCheck = $this->hasPropertySerializationStrategies
            && (!$this->requireExclusionCheck || ($this->requireExclusionCheck && $this->hasPropertyExclusionCheck));
        $exclusionData = $classExclusionCheck || $propertyExclusionCheck
            ? new DefaultSerializationExclusionData($value, $context)
            : null;

        if ($classExclusionCheck && $exclusionData) {
            $this->excluder->applyClassSerializationExclusionData($exclusionData);

            if ($this->excluder->excludeClassBySerializationStrategy($this->classMetadata)) {
                return null;
            }
        }

        if ($propertyExclusionCheck && $exclusionData) {
            $this->excluder->applyPropertySerializationExclusionData($exclusionData);
        }

        $enableScalarAdapters = $context->enableScalarAdapters();
        $serializeNull = $context->serializeNull();
        $result = [];

        /** @var Property $property */
        foreach ($this->properties as $property) {
            if ($property->skipSerialize) {
                continue;
            }

            $serializedName = $property->serializedName;
            $checkProperty = $this->hasPropertySerializationStrategies
                && (!$this->requireExclusionCheck || ($this->requireExclusionCheck && $property->annotations->get(ExclusionCheck::class) !== null));
            if ($checkProperty && $this->excluder->excludePropertyBySerializationStrategy($property)) {
                continue;
            }

            if (!$enableScalarAdapters && $property->isScalar) {
                $propertyValue = $property->getterStrategy->get($value);
                if ($serializeNull || $propertyValue !== null) {
                    $result[$serializedName] = $propertyValue;
                }
                continue;
            }

            $adapter = $this->adapters[$serializedName] ?? $this->getAdapter($property);
            $propertyValue = $adapter->write($property->getterStrategy->get($value), $context);
            if ($serializeNull || $propertyValue !== null) {
                $result[$serializedName] = $propertyValue;
            }
        }

        if ($this->classVirtualProperty !== null) {
            $result = [$this->classVirtualProperty => $result];
        }

        return $result;
    }

    /**
     * Get the next type adapter
     *
     * @param Property $property
     * @return TypeAdapter
     */
    protected function getAdapter(Property $property): TypeAdapter
    {
        /** @var JsonAdapter $jsonAdapterAnnotation */
        $jsonAdapterAnnotation = $property->annotations->get(JsonAdapter::class);
        $adapter = null === $jsonAdapterAnnotation
            ? $this->typeAdapterProvider->getAdapter($property->type)
            : $this->typeAdapterProvider->getAdapterFromAnnotation($property->type, $jsonAdapterAnnotation);
        $this->adapters[$property->serializedName] = $adapter;

        return $adapter;
    }
}
