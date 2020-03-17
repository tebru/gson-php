<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\TypeAdapter;

use Tebru\Gson\Context\ReaderContext;
use Tebru\Gson\Context\WriterContext;
use Tebru\Gson\Internal\Data\Property;
use Tebru\Gson\Internal\DefaultClassMetadata;
use Tebru\Gson\Internal\ObjectConstructor;
use Tebru\Gson\Internal\ObjectConstructor\CreateFromInstance;
use Tebru\Gson\Internal\ObjectConstructorAware;
use Tebru\Gson\Internal\ObjectConstructorAwareTrait;
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
     * @var DefaultClassMetadata
     */
    protected $classMetadata;

    /**
     * @var Property[]
     */
    protected $properties;

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
     * Constructor
     *
     * @param ObjectConstructor $objectConstructor
     * @param DefaultClassMetadata $classMetadata
     * @param null|string $classVirtualProperty
     */
    public function __construct(
        ObjectConstructor $objectConstructor,
        DefaultClassMetadata $classMetadata,
        ?string $classVirtualProperty
    ) {
        $this->objectConstructor = $objectConstructor;
        $this->classVirtualProperty = $classVirtualProperty;
        $this->classMetadata = $classMetadata;
        $this->properties = $classMetadata->properties->elements;
        $this->skipSerialize = $classMetadata->skipSerialize;
        $this->skipDeserialize = $classMetadata->skipDeserialize;
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

        if ($this->classVirtualProperty !== null) {
            $value = array_shift($value);
        }

        $object = $this->objectConstructor->construct();
        $usesExisting = $context->usesExistingObject;
        $enableScalarAdapters = $context->enableScalarAdapters;
        $typeAdapterProvider = $context->typeAdapterProvider;
        $excluder = $context->getExcluder();
        $payload = $context->getPayload();

        if ($this->classMetadata->hasExclusions && $excluder->skipClassDeserializeByStrategy($this->classMetadata, $object, $payload)) {
            return null;
        }

        foreach ($value as $name => $item) {
            $property = $this->properties[$name] ?? null;

            if ($property === null || $property->skipDeserialize) {
                continue;
            }

            if ($property->hasExclusions && $excluder->skipPropertyDeserializeByStrategy($property, $object, $payload)) {
                continue;
            }

            $adapter = $property->adapter;

            if ($adapter === null) {
                // disabled scalar adapters
                if ($property->isScalar && !$enableScalarAdapters) {
                    $property->setterStrategy->set($object, $item);
                    continue;
                }

                $adapter = $property->adapter = $typeAdapterProvider->getAdapterFromProperty($property);
            }

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

        $result = [];
        $serializeNull = $this->serializeNull ?? $this->serializeNull = $context->serializeNull();
        $enableScalarAdapters = $this->enableScalarAdapters ?? $this->enableScalarAdapters = $context->enableScalarAdapters();
        $typeAdapterProvider = $this->typeAdapterProvider ?? $this->typeAdapterProvider = $context->getTypeAdapterProvider();
        $excluder = $this->excluder ?? $this->excluder = $context->getExcluder();
        $extractor = $this->extractor ?? $this->extractor = \Closure::bind(function ($value, array $properties) use ($excluder, $enableScalarAdapters, $typeAdapterProvider, $serializeNull, $context) {

            $values = [];
            foreach ($properties as $property) {
                if ($property->skipSerialize) {
                    continue;
                }
                if ($property->hasExclusions && $excluder->skipPropertySerializeByStrategy($property, $value)) {
                    continue;
                }
                $serializedName = $property->serializedName;
                $propertyValue = $property->getterStrategy ? $value->{$property->getterStrategy->methodName}() : $value->{$property->realName};
                $isNull = $propertyValue === null;
                $adapter = $property->adapter;
                if ($adapter === null && ($enableScalarAdapters || !$property->isScalar)) {
                    $adapter = $typeAdapterProvider->getAdapterFromProperty($property);
                }
                if (!$isNull && $adapter !== null) {
                    $propertyValue = $adapter->write($propertyValue, $context);
                }

                if ($serializeNull || !$isNull) {
                    $values[$serializedName] = $propertyValue;
                }
            }

            return $values;
            }, null, $this->classMetadata->name);

        if ($this->classMetadata->hasExclusions && $excluder->skipClassSerializeByStrategy($this->classMetadata, $value)) {
            return null;
        }

//        $propertys = [];
//        foreach ($this->properties as $property) {
//            if ($property->skipSerialize) {
//                continue;
//            }
//            if ($property->hasExclusions && $excluder->skipPropertySerializeByStrategy($property, $value)) {
//                continue;
//            }
//            $propertys[] = $property;
//            continue;
//
//            $serializedName = $property->serializedName;
//            $propertyValue = $value->{$property->getterStrategy->propertyName};
//            $isNull = $propertyValue === null;
//            $adapter = $property->adapter;
//
//            if ($adapter === null && ($enableScalarAdapters || !$property->isScalar)) {
//                $adapter = $typeAdapterProvider->getAdapterFromProperty($property);
//            }
//
//            if (!$isNull && $adapter !== null) {
//                $propertyValue = $adapter->write($propertyValue, $context);
//            }
//
//            if ($serializeNull || !$isNull) {
//                $result[$serializedName] = $propertyValue;
//            }
//        }

        $result = $extractor($value, $this->properties);

        if ($this->classVirtualProperty !== null) {
            $result = [$this->classVirtualProperty => $result];
        }

        return $result;
    }

    /**
     * Return true if object can be written to disk
     *
     * @return bool
     */
    public function canCache(): bool
    {
        return $this->objectConstructor->canCache();
    }
}
