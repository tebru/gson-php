<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson;

// @codeCoverageIgnoreStart
@trigger_error('Gson: \Tebru\Gson\ExclusionStrategy is deprecated since v0.6.0 and will be removed in v0.7.0. Use \Tebru\Gson\Exclusion\ExclusionStrategy instead.', E_USER_DEPRECATED);
// @codeCoverageIgnoreEnd

/**
 * Interface ExclusionStrategy
 *
 * A strategy to determine if a class or class property should be serialized or deserialized
 *
 * @author Nate Brunette <n@tebru.net>
 * @deprecated Since v0.6.0 to be removed in v0.7.0. Use \Tebru\Gson\Exclusion\ExclusionStrategy instead.
 */
interface ExclusionStrategy
{
    /**
     * Return true if the class should be ignored
     *
     * @param ClassMetadata $classMetadata
     * @param ExclusionData $exclusionData
     * @return bool
     * @deprecated Since v0.6.0 to be removed in v0.7.0. Use \Tebru\Gson\Exclusion\ExclusionStrategy instead.
     */
    public function shouldSkipClass(ClassMetadata $classMetadata, ExclusionData $exclusionData): bool;

    /**
     * Return true if the property should be ignored
     *
     * @param PropertyMetadata $propertyMetadata
     * @param ExclusionData $exclusionData
     * @return bool
     * @deprecated Since v0.6.0 to be removed in v0.7.0. Use \Tebru\Gson\Exclusion\ExclusionStrategy instead.
     */
    public function shouldSkipProperty(PropertyMetadata $propertyMetadata, ExclusionData $exclusionData): bool;
}
