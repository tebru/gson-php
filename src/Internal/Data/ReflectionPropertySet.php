<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal\Data;

use ReflectionProperty;
use Tebru\Collection\HashSet;

/**
 * Class ReflectionPropertySet
 *
 * A [@see HashSet] that is keyed by [@see \ReflectionProperty] name
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class ReflectionPropertySet extends HashSet
{
    /**
     * Return the key to use for the HashMap
     *
     * @param ReflectionProperty $element
     * @return mixed
     */
    public function getKey($element)
    {
        return $element->getName();
    }
}
