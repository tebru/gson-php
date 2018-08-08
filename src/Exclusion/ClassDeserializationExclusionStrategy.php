<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Exclusion;

use Tebru\Gson\ClassMetadata;

/**
 * Interface ClassDeserializationExclusionStrategy
 *
 * Determines if a class should be skipped during deserialization
 *
 * @author Nate Brunette <n@tebru.net>
 */
interface ClassDeserializationExclusionStrategy extends ExclusionStrategy
{
    /**
     * Returns true if the class should be skipped during deserialization
     *
     * @param ClassMetadata $class
     * @return bool
     */
    public function skipDeserializingClass(ClassMetadata $class): bool;
}
