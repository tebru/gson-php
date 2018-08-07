<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal;

use JsonSerializable;
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
final class JsonElementWriter extends JsonWriter implements JsonSerializable
{
    /**
     * Begin writing array
     *
     * @return JsonWritable
     * @throws \LogicException
     */
    public function beginArray(): JsonWritable
    {
        if ($this->stackSize > 0 && $this->stackStates[$this->stackSize - 1] === self::STATE_OBJECT_NAME) {
            $this->assertionFailed('Cannot call beginArray() before name() during object serialization');
        }

        $array = new JsonArray();
        $this->push($array);
        $this->stack[] = $array;
        $this->stackStates[$this->stackSize] = self::STATE_ARRAY;
        $this->stackSize++;
        $this->pathIndices[$this->pathIndex++]++;
        $this->pathIndices[$this->pathIndex] = -1;

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
        if ($this->stackSize === 0 || $this->stackStates[$this->stackSize - 1] !== self::STATE_ARRAY) {
            $this->assertionFailed('Cannot call endArray() if not serializing array');
        }

        \array_pop($this->stack);
        $this->stackSize--;
        $this->pathIndex--;

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
        if ($this->stackSize > 0 && $this->stackStates[$this->stackSize - 1] === self::STATE_OBJECT_NAME) {
            $this->assertionFailed('Cannot call beginObject() before name() during object serialization');
        }

        $class = new JsonObject();
        $this->push($class);
        $this->stack[$this->stackSize] = $class;
        $this->stackStates[$this->stackSize] = self::STATE_OBJECT_NAME;
        $this->stackSize++;
        $this->pathIndices[$this->pathIndex++]++;
        $this->pathIndices[$this->pathIndex] = -1;

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
        if ($this->stackSize === 0 || $this->stackStates[$this->stackSize - 1] !== self::STATE_OBJECT_NAME) {
            $this->assertionFailed('Cannot call endObject() if not serializing object');
        }

        \array_pop($this->stack);
        $this->stackSize--;
        $this->pathNames[$this->pathIndex--] = null;

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
        if ($this->stackSize > 0 && $this->stackStates[$this->stackSize - 1] === self::STATE_OBJECT_NAME) {
            $this->assertionFailed('Cannot call writeInteger() before name() during object serialization');
        }

        $this->pathIndices[$this->pathIndex]++;

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
        if ($this->stackSize > 0 && $this->stackStates[$this->stackSize - 1] === self::STATE_OBJECT_NAME) {
            $this->assertionFailed('Cannot call writeFloat() before name() during object serialization');
        }

        $this->pathIndices[$this->pathIndex]++;

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
        if ($this->stackSize > 0 && $this->stackStates[$this->stackSize - 1] === self::STATE_OBJECT_NAME) {
            $this->assertionFailed('Cannot call writeString() before name() during object serialization');
        }

        $this->pathIndices[$this->pathIndex]++;

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
        if ($this->stackSize > 0 && $this->stackStates[$this->stackSize - 1] === self::STATE_OBJECT_NAME) {
            $this->assertionFailed('Cannot call writeBoolean() before name() during object serialization');
        }

        $this->pathIndices[$this->pathIndex]++;

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
        if ($this->stackSize > 0 && $this->stackStates[$this->stackSize - 1] === self::STATE_OBJECT_NAME) {
            $this->assertionFailed('Cannot call writeNull() before name() during object serialization');
        }

        if ($this->serializeNull) {
            $this->pathIndices[$this->pathIndex]++;
            return $this->push(new JsonNull());
        }

        // if we're not serializing nulls
        if (null !== $this->pendingName) {
            $this->stackStates[$this->stackSize - 1] = self::STATE_OBJECT_NAME;
            $this->pendingName = null;
        }

        return $this;
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
                $this->assertionFailed('Attempting to write two different types');
            }

            $this->result = $value;

            return $this;
        }

        switch ($this->stackStates[$this->stackSize - 1]) {
            case self::STATE_OBJECT_VALUE:
                /** @var JsonObject $element */
                $element = $this->stack[$this->stackSize - 1];
                $element->add($this->pendingName, $value);
                $this->stackStates[$this->stackSize - 1] = self::STATE_OBJECT_NAME;
                $this->pendingName = null;
                break;
            case self::STATE_ARRAY:
                /** @var JsonArray $element */
                $element = $this->stack[$this->stackSize - 1];
                $element->addJsonElement($value);
                $this->stackStates[$this->stackSize - 1] = self::STATE_ARRAY;
                break;
        }

        return $this;
    }
}
