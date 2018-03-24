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
use LogicException;
use stdClass;

/**
 * Class JsonObject
 *
 * Represents a json object
 *
 * @author Nate Brunette <n@tebru.net>
 */
class JsonObject extends JsonElement implements IteratorAggregate, Countable
{
    /**
     * Object properties
     *
     * @var JsonElement[]
     */
    private $properties = [];

    /**
     * Add a string to object at property
     *
     * @param string $property
     * @param string $value
     */
    public function addString(string $property, ?string $value)
    {
        $this->add($property, JsonPrimitive::create($value));
    }

    /**
     * Add an integer to object at property
     *
     * @param string $property
     * @param int $value
     */
    public function addInteger(string $property, ?int $value)
    {
        $this->add($property, JsonPrimitive::create($value));
    }

    /**
     * Add a float to object at property
     *
     * @param string $property
     * @param float $value
     */
    public function addFloat(string $property, ?float $value)
    {
        $this->add($property, JsonPrimitive::create($value));
    }

    /**
     * Add a boolean to object at property
     *
     * @param string $property
     * @param bool $value
     */
    public function addBoolean(string $property, ?bool $value)
    {
        $this->add($property, JsonPrimitive::create($value));
    }

    /**
     * Add an element to object at property
     *
     * @param string $property
     * @param JsonElement $jsonElement
     */
    public function add(string $property, JsonElement $jsonElement)
    {
        $this->properties[$property] = $jsonElement;
    }

    /**
     * Returns true if the object has property
     *
     * @param string $property
     * @return bool
     */
    public function has(string $property): bool
    {
        return isset($this->properties[$property]);
    }

    /**
     * Get the value at property
     *
     * @param string $property
     * @return JsonElement
     */
    public function get(string $property): JsonElement
    {
        return $this->properties[$property];
    }

    /**
     * Convenience method to get a value and ensure it's a primitive
     *
     * @param string $property
     * @return JsonPrimitive
     * @throws \LogicException
     */
    public function getAsJsonPrimitive(string $property): JsonPrimitive
    {
        /** @var JsonPrimitive $jsonElement */
        $jsonElement = $this->properties[$property];

        if (!$jsonElement->isJsonPrimitive()) {
            throw new LogicException('This value is not a primitive');
        }

        return $jsonElement;
    }

    /**
     * Convenience method to get a value and ensure it's an object
     *
     * @param string $property
     * @return JsonObject
     * @throws \LogicException
     */
    public function getAsJsonObject(string $property): JsonObject
    {
        /** @var JsonObject $jsonElement */
        $jsonElement = $this->properties[$property];

        if (!$jsonElement->isJsonObject()) {
            throw new LogicException('This value is not an object');
        }

        return $jsonElement;
    }

    /**
     * Convenience method to get a value and ensure it's an array
     *
     * @param string $property
     * @return JsonArray
     * @throws \LogicException
     */
    public function getAsJsonArray(string $property): JsonArray
    {
        /** @var JsonArray $jsonElement */
        $jsonElement = $this->properties[$property];

        if (!$jsonElement->isJsonArray()) {
            throw new LogicException('This value is not an array');
        }

        return $jsonElement;
    }

    /**
     * Get property as string
     *
     * @param string $property
     * @return string
     */
    public function getAsString(string $property): string
    {
        return $this->getAsJsonPrimitive($property)->asString();
    }

    /**
     * Get property as integer
     *
     * @param string $property
     * @return int
     */
    public function getAsInteger(string $property): int
    {
        return $this->getAsJsonPrimitive($property)->asInteger();
    }

    /**
     * Get property as float
     *
     * @param string $property
     * @return float
     */
    public function getAsFloat(string $property): float
    {
        return $this->getAsJsonPrimitive($property)->asFloat();
    }

    /**
     * Get property as boolean
     *
     * @param string $property
     * @return boolean
     */
    public function getAsBoolean(string $property): bool
    {
        return $this->getAsJsonPrimitive($property)->asBoolean();
    }

    /**
     * Get property as array
     *
     * @param string $property
     * @return array
     */
    public function getAsArray(string $property): array
    {
        return \json_decode(\json_encode($this->get($property)), true);
    }

    /**
     * Get the value as a JsonObject
     *
     * @return JsonObject
     */
    public function asJsonObject(): JsonObject
    {
        return $this;
    }

    /**
     * Remove property from object
     *
     * @param string $property
     * @return bool
     */
    public function remove(string $property): bool
    {
        if (!isset($this->properties[$property])) {
            return false;
        }

        unset($this->properties[$property]);

        return true;
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @return stdClass
     */
    public function jsonSerialize(): stdClass
    {
        $class = new stdClass();
        foreach ($this->properties as $key => $property) {
            $class->$key = $property->jsonSerialize();
        }

        return $class;
    }

    /**
     * Retrieve an external iterator
     *
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->properties);
    }

    /**
     * Returns the number of properties in object
     *
     * @return int
     */
    public function count(): int
    {
        return \count($this->properties);
    }
}
