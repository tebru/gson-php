<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock\Strategy;

use Tebru\Gson\PropertyNamingStrategy;

/**
 * Class TwoPropertyNamingStrategy
 *
 * @author Nate Brunette <n@tebru.net>
 */
class TwoPropertyNamingStrategy implements PropertyNamingStrategy
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
        return $propertyName.'2';
    }
}
