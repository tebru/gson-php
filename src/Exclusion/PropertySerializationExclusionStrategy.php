<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Exclusion;

use Tebru\Gson\PropertyMetadata;

/**
 * Interface PropertySerializationExclusionStrategy
 *
 * Determines if properties should be skipped during serialization
 *
 * @author Nate Brunette <n@tebru.net>
 */
interface PropertySerializationExclusionStrategy extends ExclusionStrategy
{
    /**
     * Returns true if the property should be skipped during serialization
     *
     * @param PropertyMetadata $property
     * @return bool
     */
    public function skipSerializingProperty(PropertyMetadata $property): bool;
}
