<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock\ExclusionStrategies;

use Tebru\Gson\ClassMetadata;
use Tebru\Gson\ExclusionData;
use Tebru\Gson\ExclusionStrategy;
use Tebru\Gson\PropertyMetadata;

/**
 * Class FooPropertyExclusionStrategy
 *
 * @author Nate Brunette <n@tebru.net>
 */
class FooPropertyExclusionStrategy implements ExclusionStrategy
{
    /**
     * Return true if the class should be ignored
     *
     * @param ClassMetadata $classMetadata
     * @param ExclusionData $exclusionData
     * @return bool
     */
    public function shouldSkipClass(ClassMetadata $classMetadata, ExclusionData $exclusionData): bool
    {
        return false;
    }

    /**
     * Return true if the property should be ignored
     *
     * @param PropertyMetadata $propertyMetadata
     * @param ExclusionData $exclusionData
     * @return bool
     */
    public function shouldSkipProperty(PropertyMetadata $propertyMetadata, ExclusionData $exclusionData): bool
    {
        return 'foo' === $propertyMetadata->getName();
    }
}
