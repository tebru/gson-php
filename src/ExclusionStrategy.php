<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson;

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
     * @param string $className
     * @return bool
     */
    public function shouldSkipClass(string $className): bool;

    /**
     * Return true if the property should be ignored
     *
     * @param string $className
     * @param string $propertyName
     * @return bool
     */
    public function shouldSkipProperty(string $className, string $propertyName): bool;
}
