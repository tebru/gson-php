<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal\Naming;

use Tebru\Gson\PropertyNamingStrategy;

/**
 * Class SnakePropertyNamingStrategy
 *
 * Converts camelCase property names to snake_case
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class SnakePropertyNamingStrategy implements PropertyNamingStrategy
{
    /**
     * Accepts the PHP class property name and returns the name that should
     * appear in json
     *
     * @param string $propertyName
     * @return string
     */
    public function translateName(string $propertyName): string
    {
        $snakeCase = [];
        $length = strlen($propertyName);
        for ($i = 0; $i < $length; ++$i) {
            $snakeCase[] = ctype_upper($propertyName[$i])
                ? '_' . strtolower($propertyName[$i])
                : strtolower($propertyName[$i]);
        }

        return implode($snakeCase);
    }
}
