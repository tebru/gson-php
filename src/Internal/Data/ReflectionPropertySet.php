<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal\Data;

use ArrayIterator;
use IteratorAggregate;
use ReflectionProperty;

/**
 * Class ReflectionPropertySet
 *
 * A [@see HashSet] that is keyed by [@see \ReflectionProperty] name
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class ReflectionPropertySet implements IteratorAggregate
{
    /**
     * @var array
     */
    private $elements = [];

    /**
     * Ensure the element exists in the collection
     *
     * Returns true if the collection can contain duplicates,
     * and false if it cannot.
     *
     * @param ReflectionProperty $element
     * @return bool
     */
    public function add(ReflectionProperty $element): bool
    {
        $key = $element->getName();
        if (isset($this->elements[$key])) {
            return false;
        }

        $this->elements[$key] = $element;

        return true;
    }

    /**
     * Returns an array of all elements in the collection
     *
     * @return array
     */
    public function toArray(): array
    {
        return \array_values($this->elements);
    }

    /**
     * Retrieve an external iterator
     *
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->toArray());
    }
}
