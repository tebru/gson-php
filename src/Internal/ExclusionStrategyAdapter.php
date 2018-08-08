<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal;

use Tebru\Gson\Cacheable;
use Tebru\Gson\ClassMetadata;
use Tebru\Gson\Exclusion\ClassDeserializationExclusionStrategy;
use Tebru\Gson\Exclusion\ClassSerializationExclusionStrategy;
use Tebru\Gson\Exclusion\DeserializationExclusionData;
use Tebru\Gson\Exclusion\DeserializationExclusionDataAware;
use Tebru\Gson\Exclusion\PropertyDeserializationExclusionStrategy;
use Tebru\Gson\Exclusion\PropertySerializationExclusionStrategy;
use Tebru\Gson\Exclusion\SerializationExclusionData;
use Tebru\Gson\Exclusion\SerializationExclusionDataAware;
use Tebru\Gson\ExclusionData;
use Tebru\Gson\ExclusionStrategy;
use Tebru\Gson\PropertyMetadata;

/**
 * Class ExclusionStrategyAdapter
 *
 * Wraps the legacy [@see ExclusionData] in new interfaces
 *
 * @author Nate Brunette <n@tebru.net>
 */
class ExclusionStrategyAdapter implements
    ClassSerializationExclusionStrategy,
    ClassDeserializationExclusionStrategy,
    PropertySerializationExclusionStrategy,
    PropertyDeserializationExclusionStrategy,
    SerializationExclusionDataAware,
    DeserializationExclusionDataAware
{
    /**
     * @var ExclusionStrategy
     */
    private $exclusionStrategy;

    /**
     * @var bool
     */
    private $serialization;

    /**
     * @var bool
     */
    private $deserialization;

    /**
     * @var SerializationExclusionData
     */
    private $serializationData;

    /**
     * @var DeserializationExclusionData
     */
    private $deserializationData;

    /**
     * Constructor
     *
     * @param ExclusionStrategy $exclusionStrategy
     * @param bool $serialization
     * @param bool $deserialization
     */
    public function __construct(ExclusionStrategy $exclusionStrategy, bool $serialization, bool $deserialization)
    {
        $this->exclusionStrategy = $exclusionStrategy;
        $this->serialization = $serialization;
        $this->deserialization = $deserialization;
    }

    /**
     * Return true if caching should be enabled
     *
     * @return bool
     */
    public function shouldCache(): bool
    {
        return $this->exclusionStrategy instanceof Cacheable && $this->exclusionStrategy->shouldCache();
    }

    /**
     * Returns true if the class should be skipped during deserialization
     *
     * @param ClassMetadata $class
     * @return bool
     */
    public function skipDeserializingClass(ClassMetadata $class): bool
    {
        return $this->deserialization && $this->deserializationData && $this->exclusionStrategy->shouldSkipClass(
            $class,
            new DefaultExclusionData(
                false,
                $this->deserializationData->getObjectToReadInto(),
                $this->deserializationData->getPayload()
            )
        );
    }

    /**
     * Returns true if the class should be skipped during serialization
     *
     * @param ClassMetadata $class
     * @return bool
     */
    public function skipSerializingClass(ClassMetadata $class): bool
    {
        return $this->serialization && $this->serializationData && $this->exclusionStrategy->shouldSkipClass(
            $class,
            new DefaultExclusionData(true, $this->serializationData->getObjectToSerialize())
        );
    }

    /**
     * Returns true if the property should be skipped during deserialization
     *
     * @param PropertyMetadata $property
     * @return bool
     */
    public function skipDeserializingProperty(PropertyMetadata $property): bool
    {
        return $this->deserialization && $this->deserializationData && $this->exclusionStrategy->shouldSkipProperty(
            $property,
            new DefaultExclusionData(
                false,
                $this->deserializationData->getObjectToReadInto(),
                $this->deserializationData->getPayload()
            )
        );
    }

    /**
     * Returns true if the property should be skipped during serialization
     *
     * @param PropertyMetadata $property
     * @return bool
     */
    public function skipSerializingProperty(PropertyMetadata $property): bool
    {
        return $this->serialization && $this->serializationData && $this->exclusionStrategy->shouldSkipProperty(
            $property,
            new DefaultExclusionData(false, $this->serializationData->getObjectToSerialize())
        );
    }

    /**
     * Sets the deserialization exclusion data
     *
     * @param DeserializationExclusionData $data
     * @return void
     */
    public function setDeserializationExclusionData(DeserializationExclusionData $data): void
    {
        $this->deserializationData = $data;
    }

    /**
     * Sets the serialization exclusion data
     *
     * @param SerializationExclusionData $data
     * @return void
     */
    public function setSerializationExclusionData(SerializationExclusionData $data): void
    {
        $this->serializationData = $data;
    }
}
