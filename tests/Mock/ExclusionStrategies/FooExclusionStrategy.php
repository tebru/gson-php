<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock\ExclusionStrategies;

use ReflectionProperty;
use Tebru\Gson\ExclusionStrategy;

/**
 * Class FooExclusionStrategy
 *
 * @author Nate Brunette <n@tebru.net>
 */
class FooExclusionStrategy implements ExclusionStrategy
{
    /**
     * Return true if the class should be ignored
     *
     * @param string $class
     * @return bool
     */
    public function shouldSkipClass(string $class): bool
    {
        return 'Foo' === $class;
    }

    /**
     * Return true if the property should be ignored
     *
     * @param ReflectionProperty $property
     * @return bool
     */
    public function shouldSkipProperty(ReflectionProperty $property): bool
    {
        return false;
    }
}