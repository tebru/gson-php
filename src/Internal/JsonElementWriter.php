<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal;

use JsonSerializable;
use LogicException;
use Tebru\Gson\Element\JsonArray;
use Tebru\Gson\Element\JsonElement;
use Tebru\Gson\Element\JsonNull;
use Tebru\Gson\Element\JsonObject;
use Tebru\Gson\Element\JsonPrimitive;
use Tebru\Gson\JsonWritable;

/**
 * Class JsonElementWriter
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class JsonElementWriter implements JsonWritable, JsonSerializable
{
    /**
     * True if we should serialize nulls
     *
     * @var bool
     */
    private $serializeNull = false;

    /**
     * Stack of values to be written
     *
     * @var array
     */
    private $stack = [];
    
    /**
     * Size of the stack array
     *
     * @var int
     */
    private $stackSize = 0;

    /**
     * When serializing an object, store the name that should be serialized
     *
     * @var
     */
    private $pendingName;

    /**
     * The final result that will be json encoded
     *
     * @var JsonElement
     */
    private $result;

    /**
     * Begin writing array
     *
     * @return JsonWritable
     * @throws \LogicException
     */
    public function beginArray(): JsonWritable
    {
        if ($this->topIsObjectStart()) {
            throw new LogicException('Cannot call beginArray() before name() during object serialization');
        }

        $array = new JsonArray();
        $this->push($array);
        $this->stack[] = $array;
        $this->stackSize++;

        return $this;
    }

    /**
     * End writing array
     *
     * @return JsonWritable
     * @throws \LogicException
     */
    public function endArray(): JsonWritable
    {
        if (!$this->topIsArray()) {
            throw new LogicException('Cannot call endArray() if not serializing array');
        }

        $this->pop();

        return $this;
    }

    /**
     * Begin writing object
     *
     * @return JsonWritable
     * @throws \LogicException
     */
    public function beginObject(): JsonWritable
    {
        if ($this->topIsObjectStart()) {
            throw new LogicException('Cannot call beginObject() before name() during object serialization');
        }

        $class = new JsonObject();
        $this->push($class);
        $this->stack[] = $class;
        $this->stackSize++;

        return $this;
    }

    /**
     * End writing object
     *
     * @return JsonWritable
     * @throws \LogicException
     */
    public function endObject(): JsonWritable
    {
        if (!$this->topIsObject()) {
            throw new LogicException('Cannot call endObject() if not serializing object');
        }

        $this->pop();

        return $this;
    }

    /**
     * Writes a property name
     *
     * @param string $name
     * @return JsonWritable
     * @throws \LogicException
     */
    public function name(string $name): JsonWritable
    {
        if (!$this->topIsObjectStart()) {
            throw new LogicException('Cannot call name() at this point.  Either name() has already been called or object serialization has not been started');
        }

        $this->pendingName = $name;

        return $this;
    }

    /**
     * Write an integer value
     *
     * @param int $value
     * @return JsonWritable
     * @throws \LogicException
     */
    public function writeInteger(int $value): JsonWritable
    {
        if ($this->topIsObjectStart()) {
            throw new LogicException('Cannot call writeInteger() before name() during object serialization');
        }

        return $this->push(JsonPrimitive::create($value));
    }

    /**
     * Write a float value
     *
     * @param float $value
     * @return JsonWritable
     * @throws \LogicException
     */
    public function writeFloat(float $value): JsonWritable
    {
        if ($this->topIsObjectStart()) {
            throw new LogicException('Cannot call writeFloat() before name() during object serialization');
        }

        return $this->push(JsonPrimitive::create($value));
    }

    /**
     * Write a string value
     *
     * @param string $value
     * @return JsonWritable
     * @throws \LogicException
     */
    public function writeString(string $value): JsonWritable
    {
        if ($this->topIsObjectStart()) {
            throw new LogicException('Cannot call writeString() before name() during object serialization');
        }

        return $this->push(JsonPrimitive::create($value));
    }

    /**
     * Write a boolean value
     *
     * @param boolean $value
     * @return JsonWritable
     * @throws \LogicException
     */
    public function writeBoolean(bool $value): JsonWritable
    {
        if ($this->topIsObjectStart()) {
            throw new LogicException('Cannot call writeBoolean() before name() during object serialization');
        }

        return $this->push(JsonPrimitive::create($value));
    }

    /**
     * Write a null value if we are serializing nulls, otherwise
     * skip the value.  If this is a property value, that property
     * should be skipped as well.
     *
     * @return JsonWritable
     * @throws \LogicException
     */
    public function writeNull(): JsonWritable
    {
        if ($this->topIsObjectStart()) {
            throw new LogicException('Cannot call writeNull() before name() during object serialization');
        }

        if ($this->serializeNull) {
            return $this->push(new JsonNull());
        }

        // if we're not serializing nulls
        if (null !== $this->pendingName) {
            $this->pendingName = null;
        }

        return $this;
    }

    /**
     * Sets whether nulls are serialized
     *
     * @param bool $serializeNull
     * @return void
     */
    public function setSerializeNull(bool $serializeNull): void
    {
        $this->serializeNull = $serializeNull;
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @return mixed
     */
    public function jsonSerialize()
    {
        if (null === $this->result) {
            return null;
        }

        return $this->result->jsonSerialize();
    }

    /**
     * Get the result as a json element
     *
     * @return JsonElement
     */
    public function toJsonElement(): JsonElement
    {
        return $this->result;
    }

    /**
     * Get the last index of the stack
     *
     * @return int
     */
    private function last(): int
    {
        return $this->stackSize - 1;
    }

    /**
     * Push a value to the result or current array/object
     *
     * @param JsonElement $value
     * @return JsonWritable
     * @throws \LogicException
     */
    private function push(JsonElement $value): JsonWritable
    {
        if (0 === $this->stackSize) {
            if (null !== $this->result) {
                throw new LogicException('Attempting to write two different types');
            }

            $this->result = $value;

            return $this;
        }

        if (null !== $this->pendingName) {
            $this->stack[$this->last()]->add($this->pendingName, $value);
            $this->pendingName = null;
        }

        if ($this->topIsArray()) {
            $this->stack[$this->last()]->addJsonElement($value);
        }

        return $this;
    }

    /**
     * Remove the last element of the stack
     */
    private function pop(): void
    {
        \array_splice($this->stack, $this->last(), 1);
        $this->stackSize--;
    }

    /**
     * Returns true if an object is the top element of the stack and we haven't called name() yet
     *
     * @return bool
     */
    private function topIsObjectStart(): bool
    {
        if (0 === $this->stackSize) {
            return false;
        }

        return $this->stack[$this->last()] instanceof JsonObject && null === $this->pendingName;
    }

    /**
     * Returns true if an object is the top element of the stack
     *
     * @return bool
     */
    private function topIsObject(): bool
    {
        if (0 === $this->stackSize) {
            return false;
        }

        return $this->stack[$this->last()] instanceof JsonObject;
    }

    /**
     * Returns true if an array is the top element of the stack
     *
     * @return bool
     */
    private function topIsArray(): bool
    {
        if (0 === $this->stackSize) {
            return false;
        }

        return $this->stack[$this->last()] instanceof JsonArray;
    }
}
