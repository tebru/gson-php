<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson;

/**
 * Interface PropertyNamingStrategy
 *
 * Define an alternate strategy to convert property names to serialized names
 *
 * @author Nate Brunette <n@tebru.net>
 */
interface PropertyNamingStrategy
{
    /**
     * Accepts the PHP class property name and returns the name that should
     * appear in json
     *
     * @param string $propertyName
     * @return string
     */
    public function translateName(string $propertyName): string;
}
