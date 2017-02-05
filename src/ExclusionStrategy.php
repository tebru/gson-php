<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson;

use ReflectionProperty;

/**
 * Interface ExclusionStrategy
 *
 * A strategy to determine if a class or class property should be serialized or deserialized
 *
 * @author Nate Brunette <n@tebru.net>
 */
interface ExclusionStrategy
{
    /**
     * Return true if the class should be ignored
     *
     * @param string $class
     * @return bool
     */
    public function shouldSkipClass(string $class): bool;

    /**
     * Return true if the property should be ignored
     *
     * @param ReflectionProperty $property
     * @return bool
     */
    public function shouldSkipProperty(ReflectionProperty $property): bool;
}
