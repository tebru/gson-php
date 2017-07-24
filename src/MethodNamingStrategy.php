<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson;

/**
 * Interface MethodNamingStrategy
 *
 * Define an alternate strategy to convert property names to getters/setters
 *
 * @author Nate Brunette <n@tebru.net>
 */
interface MethodNamingStrategy
{
    /**
     * Accepts the PHP class property name and returns an array of the names
     * of acceptable getter methods
     *
     * @param string $propertyName
     * @return array
     */
    public function translateToGetter(string $propertyName): array;

    /**
     * Accepts the PHP class property name and returns an array of the names
     * of acceptable setter methods
     *
     * @param string $propertyName
     * @return array
     */
    public function translateToSetter(string $propertyName): array;
}
