<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock\ExclusionStrategies;

use Tebru\Gson\Exclusion\PropertyDeserializationExclusionStrategy;
use Tebru\Gson\Exclusion\PropertySerializationExclusionStrategy;
use Tebru\Gson\PropertyMetadata;

/**
 * Class BarPropertyExclusionStrategy
 *
 * @author Nate Brunette <n@tebru.net>
 */
class BarPropertyExclusionStrategy implements PropertySerializationExclusionStrategy, PropertyDeserializationExclusionStrategy
{
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
        return 'bar' === $property->getName();
    }

    /**
     * Returns true if the property should be skipped during serialization
     *
     * @param PropertyMetadata $property
     * @return bool
     */
    public function skipSerializingProperty(PropertyMetadata $property): bool
    {
        return 'bar' === $property->getName();
    }
}
