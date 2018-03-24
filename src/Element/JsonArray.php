<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Element;

use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 * Class JsonArray
 *
 * Represents a json array
 *
 * @author Nate Brunette <n@tebru.net>
 */
class JsonArray extends JsonElement implements IteratorAggregate, Countable
{
    /**
     * Array values
     *
     * @var JsonElement[]
     */
    private $values = [];

    /**
     * Add a string to array
     *
     * @param string $value
     */
    public function addString(?string $value): void
    {
        $this->addJsonElement(JsonPrimitive::create($value));
    }

    /**
     * Add an integer to array
     *
     * @param int $value
     */
    public function addInteger(?int $value): void
    {
        $this->addJsonElement(JsonPrimitive::create($value));
    }

    /**
     * Add a float to array
     *
     * @param float $value
     */
    public function addFloat(?float $value): void
    {
        $this->addJsonElement(JsonPrimitive::create($value));
    }

    /**
     * Add a boolean to array
     *
     * @param bool $value
     */
    public function addBoolean(?bool $value): void
    {
        $this->addJsonElement(JsonPrimitive::create($value));
    }

    /**
     * Add another json element to array
     *
     * @param JsonElement $jsonElement
     */
    public function addJsonElement(JsonElement $jsonElement)
    {
        $this->values[] = $jsonElement;
    }

    /**
     * Add all elements from another json array
     *
     * @param JsonArray $jsonArray
     */
    public function addAll(JsonArray $jsonArray): void
    {
        foreach ($jsonArray as $jsonElement) {
            $this->addJsonElement($jsonElement);
        }
    }

    /**
     * Get the value as a JsonArray
     *
     * @return JsonArray
     */
    public function asJsonArray(): JsonArray
    {
        return $this;
    }

    /**
     * Returns true if the element currently exists in the array
     *
     * @param JsonElement $jsonElement
     * @return bool
     */
    public function contains(JsonElement $jsonElement): bool
    {
        foreach ($this->values as $element) {
            if ($element === $jsonElement) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if the index exists
     *
     * @param int $index
     * @return bool
     */
    public function has(int $index): bool
    {
        return isset($this->values[$index]);
    }

    /**
     * Returns the element at the index
     *
     * @param int $index
     * @return JsonElement
     */
    public function get(int $index): JsonElement
    {
        return $this->values[$index];
    }

    /**
     * Sets an element at the index
     *
     * @param int $index
     * @param JsonElement $jsonElement
     */
    public function set(int $index, JsonElement $jsonElement): void
    {
        $this->values[$index] = $jsonElement;
    }

    /**
     * Removes the specified element
     *
     * @param JsonElement $jsonElement
     * @return bool
     */
    public function remove(JsonElement $jsonElement): bool
    {
        foreach ($this->values as $index => $element) {
            if ($element === $jsonElement) {
                unset($this->values[$index]);
                return true;
            }
        }

        return false;
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @return mixed
     */
    public function jsonSerialize()
    {
        $array = [];
        foreach ($this->values as $value) {
            $array[] = $value->jsonSerialize();
        }

        return $array;
    }

    /**
     * Get an iterator for the array
     *
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->values);
    }

    /**
     * Returns the number of elements in array
     *
     * @return int
     */
    public function count(): int
    {
        return \count($this->values);
    }
}
