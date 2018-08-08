<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Exclusion;

use Tebru\Gson\PropertyMetadata;

/**
 * Interface PropertyDeserializationExclusionStrategy
 *
 * Determines if properties should be skipped during deserialization
 *
 * @author Nate Brunette <n@tebru.net>
 */
interface PropertyDeserializationExclusionStrategy extends ExclusionStrategy
{
    /**
     * Returns true if the property should be skipped during deserialization
     *
     * @param PropertyMetadata $property
     * @return bool
     */
    public function skipDeserializingProperty(PropertyMetadata $property): bool;
}
