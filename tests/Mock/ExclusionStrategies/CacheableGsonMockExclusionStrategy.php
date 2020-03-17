<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Test\Mock\ExclusionStrategies;

use Tebru\Gson\ClassMetadata;
use Tebru\Gson\Exclusion\ExclusionStrategy;
use Tebru\Gson\PropertyMetadata;
use Tebru\Gson\Test\Mock\GsonMock;

/**
 * Class CacheableGsonMockExclusionStrategy
 *
 * @author Nate Brunette <n@tebru.net>
 */
class CacheableGsonMockExclusionStrategy implements ExclusionStrategy
{
    /**
     * Returns true if the property should be skipped during deserialization
     *
     * @param PropertyMetadata $property
     * @param null $object
     * @param null $payload
     * @return bool
     */
    public function skipDeserializingProperty(PropertyMetadata $property, $object = null, $payload = null): bool
    {
        return $property->getDeclaringClassName() === GsonMock::class
            && $property->getName() === 'excludeFromStrategy';
    }

    /**
     * Returns true if the property should be skipped during serialization
     *
     * @param PropertyMetadata $property
     * @param null $object
     * @return bool
     */
    public function skipSerializingProperty(PropertyMetadata $property, $object = null): bool
    {
        return $property->getDeclaringClassName() === GsonMock::class
            && $property->getName() === 'excludeFromStrategy';
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
     * Return true if object can be written to disk
     *
     * @return bool
     */
    public function canCache(): bool
    {
        return true;
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
