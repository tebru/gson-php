<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock\ExclusionStrategies;

use Tebru\Gson\ClassMetadata;
use Tebru\Gson\Exclusion\ExclusionStrategy;
use Tebru\Gson\PropertyMetadata;

/**
 * Class UserEmailExclusionStrategy
 *
 * @author Nate Brunette <n@tebru.net>
 */
class UserEmailExclusionStrategy implements ExclusionStrategy
{

    /**
     * Returns true if the property should be skipped during serialization
     *
     * @param PropertyMetadata $property
     * @param null $object
     * @param null $payload
     * @return bool
     */
    public function skipDeserializingProperty(PropertyMetadata $property, $object = null, $payload = null): bool
    {
        return $property->getName() === 'email';
    }

    /**
     * Returns true if the property should be skipped during deserialization
     *
     * @param PropertyMetadata $property
     * @param null $object
     * @return bool
     */
    public function skipSerializingProperty(PropertyMetadata $property, $object = null): bool
    {
        return $property->getName() === 'email';
    }

    /**
     * Return true if object can be written to disk
     *
     * @return bool
     */
    public function canCache(): bool
    {
        return true;
    }

    /**
     * Returns true if the class should be skipped during serialization
     *
     * @param ClassMetadata $class
     * @param object|null $object
     * @return bool
     */
    public function skipSerializingClass(ClassMetadata $class, $object = null): bool
    {
        return false;
    }

    /**
     * Returns true if the class should be skipped during deserialization
     *
     * @param ClassMetadata $class
     * @param object|null $object
     * @param null $payload
     * @return bool
     */
    public function skipDeserializingClass(ClassMetadata $class, $object = null, $payload = null): bool
    {
        return false;
    }

    /**
     * Returns true if the result of the strategy should be cached
     *
     * @return bool
     */
    public function cacheResult(): bool
    {
        return true;
    }
}
