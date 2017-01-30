<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal\Data;

use ArrayIterator;
use Tebru\Collection\AbstractSet;

/**
 * Class ClassNameSet
 *
 * A HashSet that is keyed by class name
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class AnnotationSet extends AbstractSet
{
    /**
     * @var array
     */
    private $elements = [];

    /**
     * Constructor
     *
     * @param array $elements
     */
    public function __construct(array $elements = [])
    {
        foreach ($elements as $element) {
            $this->add($element);
        }
    }

    /**
     * Get an annotation by class name
     *
     * @param string $annotationClass
     * @return object|null
     */
    public function getAnnotation(string $annotationClass)
    {
        if (!array_key_exists($annotationClass, $this->elements)) {
            return null;
        }

        return $this->elements[$annotationClass];
    }

    /**
     * Ensure the element exists in the collection
     *
     * Returns true if the collection can contain duplicates,
     * and false if it cannot.
     *
     * @param mixed $element
     * @return bool
     */
    public function add($element): bool
    {
        if ($this->contains($element)) {
            return false;
        }

        $key = get_class($element);
        $this->elements[$key] = $element;

        return true;
    }

    /**
     * Removes all elements from a collection
     *
     * @return void
     */
    public function clear(): void
    {
        $this->elements = [];
    }

    /**
     * Returns true if the element exists
     *
     * @param object $element
     * @return bool
     */
    public function contains($element): bool
    {
        $key = get_class($element);

        return array_key_exists($key, $this->elements);
    }

    /**
     * Removes object if it exists
     *
     * Returns true if the element was removed
     *
     * @param mixed $element
     * @return bool
     */
    public function remove($element): bool
    {
        $key = get_class($element);

        if (!array_key_exists($key, $this->elements)) {
            return false;
        }

        unset($this->elements[$key]);

        return true;
    }


    /**
     * Returns an array of all elements in the collection
     *
     * @return array
     */
    public function toArray(): array
    {
        return array_values($this->elements);
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
