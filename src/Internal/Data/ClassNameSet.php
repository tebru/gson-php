<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal\Data;

use Tebru\Collection\HashSet;

/**
 * Class ClassNameSet
 *
 * A HashSet that is keyed by class name
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class ClassNameSet extends HashSet
{
    /**
     * Return the key to use for the HashMap
     *
     * @param object $element
     * @return string
     */
    public function getKey($element): string
    {
        return get_class($element);
    }
}
