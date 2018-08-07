<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Test\Mock\ExclusionStrategies;

use Tebru\Gson\Exclusion\PropertySerializationExclusionStrategy;
use Tebru\Gson\Exclusion\SerializationExclusionData;
use Tebru\Gson\Exclusion\SerializationExclusionDataAware;
use Tebru\Gson\PropertyMetadata;

/**
 * Class CacheableDataAwareExclusionStrategy
 *
 * @author Nate Brunette <n@tebru.net>
 */
class CacheableDataAwareExclusionStrategy implements PropertySerializationExclusionStrategy, SerializationExclusionDataAware
{
    /**
     * Return true if the result of the strategy should be cached
     *
     * @return bool
     */
    public function shouldCache(): bool
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
     * Sets the serialization exclusion data
     *
     * @param SerializationExclusionData $data
     * @return void
     */
    public function setSerializationExclusionData(SerializationExclusionData $data): void
    {
    }
}
