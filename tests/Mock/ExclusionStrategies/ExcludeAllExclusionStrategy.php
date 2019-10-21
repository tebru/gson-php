<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Test\Mock\ExclusionStrategies;

use Tebru\Gson\ClassMetadata;
use Tebru\Gson\Exclusion\ClassDeserializationExclusionStrategy;
use Tebru\Gson\Exclusion\ClassSerializationExclusionStrategy;
use Tebru\Gson\Exclusion\DeserializationExclusionData;
use Tebru\Gson\Exclusion\DeserializationExclusionDataAware;
use Tebru\Gson\Exclusion\PropertyDeserializationExclusionStrategy;
use Tebru\Gson\Exclusion\PropertySerializationExclusionStrategy;
use Tebru\Gson\Exclusion\SerializationExclusionData;
use Tebru\Gson\Exclusion\SerializationExclusionDataAware;
use Tebru\Gson\PropertyMetadata;

/**
 * Class ExcludeAllExclusionStrategy
 *
 * @author Nate Brunette <n@tebru.net>
 */
class ExcludeAllExclusionStrategy implements
    ClassSerializationExclusionStrategy,
    ClassDeserializationExclusionStrategy,
    PropertySerializationExclusionStrategy,
    PropertyDeserializationExclusionStrategy,
    SerializationExclusionDataAware,
    DeserializationExclusionDataAware
{
    public $calledSerialize = false;
    public $calledDeserialize =false;

    /**
     * Returns true if the class should be skipped during deserialization
     *
     * @param ClassMetadata $class
     * @return bool
     */
    public function skipDeserializingClass(ClassMetadata $class): bool
    {
        return true;
    }

    /**
     * Returns true if the class should be skipped during serialization
     *
     * @param ClassMetadata $class
     * @return bool
     */
    public function skipSerializingClass(ClassMetadata $class): bool
    {
        return true;
    }

    /**
     * Return true if the result of the strategy should be cached
     *
     * @return bool
     */
    public function shouldCache(): bool
    {
        return false;
    }

    /**
     * Returns true if the property should be skipped during deserialization
     *
     * @param PropertyMetadata $property
     * @return bool
     */
    public function skipDeserializingProperty(PropertyMetadata $property): bool
    {
        return true;
    }

    /**
     * Returns true if the property should be skipped during serialization
     *
     * @param PropertyMetadata $property
     * @return bool
     */
    public function skipSerializingProperty(PropertyMetadata $property): bool
    {
        return true;
    }

    /**
     * Sets the deserialization exclusion data
     *
     * @param DeserializationExclusionData $data
     * @return void
     */
    public function setDeserializationExclusionData(DeserializationExclusionData $data): void
    {
        $this->calledDeserialize = true;
    }

    /**
     * Sets the serialization exclusion data
     *
     * @param SerializationExclusionData $data
     * @return void
     */
    public function setSerializationExclusionData(SerializationExclusionData $data): void
    {
        $this->calledSerialize = true;
    }
}
