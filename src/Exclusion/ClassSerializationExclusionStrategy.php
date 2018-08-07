<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Exclusion;

use Tebru\Gson\ClassMetadata;

/**
 * Interface ClassSerializationExclusionStrategy
 *
 * Determines if a class should be skipped during serialization
 *
 * @author Nate Brunette <n@tebru.net>
 */
interface ClassSerializationExclusionStrategy extends ExclusionStrategy
{
    /**
     * Returns true if the class should be skipped during serialization
     *
     * @param ClassMetadata $class
     * @return bool
     */
    public function skipSerializingClass(ClassMetadata $class): bool;
}
