<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock\ExclusionStrategies;

use Tebru\Gson\ExclusionStrategy;
use Tebru\Gson\Test\Mock\GsonMock;

/**
 * Class GsonMockExclusionStrategyMock
 *
 * @author Nate Brunette <n@tebru.net>
 */
class GsonMockExclusionStrategyMock implements ExclusionStrategy
{
    /**
     * Return true if the class should be ignored
     *
     * @param string $className
     * @return bool
     */
    public function shouldSkipClass(string $className): bool
    {
        return false;
    }

    /**
     * Return true if the property should be ignored
     *
     * @param string $className
     * @param string $propertyName
     * @return bool
     */
    public function shouldSkipProperty(string $className, string $propertyName): bool
    {
        return $className === GsonMock::class && $propertyName === 'excludeFromStrategy';
    }
}
