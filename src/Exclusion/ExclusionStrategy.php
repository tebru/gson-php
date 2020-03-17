<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Exclusion;

use Tebru\Gson\Cacheable;
use Tebru\Gson\ClassMetadata;
use Tebru\Gson\PropertyMetadata;

/**
 * Interface ExclusionStrategy
 *
 * @author Nate Brunette <n@tebru.net>
 */
interface ExclusionStrategy extends Cacheable
{
    /**
     * Returns true if the class should be skipped during serialization
     *
     * @param ClassMetadata $class
     * @param object|null $object
     * @return bool
     */
    public function skipSerializingClass(ClassMetadata $class, $object = null): bool;

    /**
     * Returns true if the class should be skipped during deserialization
     *
     * @param ClassMetadata $class
     * @param object|null $object
     * @param null $payload
     * @return bool
     */
    public function skipDeserializingClass(ClassMetadata $class, $object = null, $payload = null): bool;

    /**
     * Returns true if the property should be skipped during serialization
     *
     * @param PropertyMetadata $property
     * @param object|null $object
     * @return bool
     */
    public function skipSerializingProperty(PropertyMetadata $property, $object = null): bool;

    /**
     * Returns true if the property should be skipped during deserialization
     *
     * @param PropertyMetadata $property
     * @param object|null $object
     * @param null $payload
     * @return bool
     */
    public function skipDeserializingProperty(PropertyMetadata $property, $object = null, $payload = null): bool;

    /**
     * Returns true if the result of the strategy should be cached
     *
     * @return bool
     */
    public function cacheResult(): bool;
}
