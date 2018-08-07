<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal;

use LogicException;
use stdClass;
use Tebru\Gson\JsonWritable;

/**
 * Class JsonEncodeWriter
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class JsonEncodeWriter implements JsonWritable
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
     * A cache of the parsing state corresponding to the stack
     *
     * @var int[]
     */
    private $stackStates = [];

    /**
     * When serializing an object, store the name that should be serialized
     *
     * @var
     */
    private $pendingName;

    /**
     * The final result that will be json encoded
     *
     * @var mixed
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
        if ($this->stackSize > 0 && $this->stackStates[$this->stackSize - 1] === self::STATE_OBJECT_NAME) {
            throw new LogicException('Cannot call beginArray() before name() during object serialization');
        }

        $array = [];
        $this->push($array);
        $this->stack[$this->stackSize] = &$array;
        $this->stackStates[$this->stackSize] = self::STATE_ARRAY;
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
        if ($this->stackSize === 0 || $this->stackStates[$this->stackSize - 1] !== self::STATE_ARRAY) {
            throw new LogicException('Cannot call endArray() if not serializing array');
        }

        \array_pop($this->stack);
        $this->stackSize--;

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
            throw new LogicException('Cannot call beginObject() before name() during object serialization');
        }

        $class = new stdClass();
        $this->push($class);
        $this->stack[$this->stackSize] = $class;
        $this->stackStates[$this->stackSize] = self::STATE_OBJECT_NAME;
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
        if ($this->stackSize === 0 || $this->stackStates[$this->stackSize - 1] !== self::STATE_OBJECT_NAME) {
            throw new LogicException('Cannot call endObject() if not serializing object');
        }

        \array_pop($this->stack);
        $this->stackSize--;

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
        if ($this->stackStates[$this->stackSize - 1] !== self::STATE_OBJECT_NAME) {
            throw new LogicException('Cannot call name() at this point.  Either name() has already been called or object serialization has not been started');
        }

        $this->pendingName = $name;
        $this->stackStates[$this->stackSize - 1] = self::STATE_OBJECT_VALUE;

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
            throw new LogicException('Cannot call writeInteger() before name() during object serialization');
        }

        return $this->push($value);
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
            throw new LogicException('Cannot call writeFloat() before name() during object serialization');
        }

        return $this->push($value);
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
            throw new LogicException('Cannot call writeString() before name() during object serialization');
        }

        return $this->push($value);
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
            throw new LogicException('Cannot call writeBoolean() before name() during object serialization');
        }

        return $this->push($value);
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
            throw new LogicException('Cannot call writeNull() before name() during object serialization');
        }

        if ($this->serializeNull) {
            $null = null;
            return $this->push($null);
        }

        // if we're not serializing nulls
        if (null !== $this->pendingName) {
            $this->stackStates[$this->stackSize - 1] = self::STATE_OBJECT_NAME;
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
     * Convert the writer to json
     *
     * @return string
     */
    public function __toString(): string
    {
        return \json_encode($this->result);
    }

    /**
     * Push a value to the result or current array/object
     *
     * @param mixed $value
     * @return JsonWritable
     * @throws \LogicException
     */
    private function push(&$value): JsonWritable
    {
        if (0 === $this->stackSize) {
            if (null !== $this->result) {
                throw new LogicException('Attempting to write two different types');
            }

            $this->result = &$value;

            return $this;
        }

        switch ($this->stackStates[$this->stackSize - 1]) {
            case self::STATE_OBJECT_VALUE:
                $this->stack[$this->stackSize - 1]->{$this->pendingName} = &$value;
                $this->stackStates[$this->stackSize - 1] = self::STATE_OBJECT_NAME;
                $this->pendingName = null;
                break;
            case self::STATE_ARRAY:
                $this->stack[$this->stackSize - 1][] = &$value;
                $this->stackStates[$this->stackSize - 1] = self::STATE_ARRAY;
                break;
        }

        return $this;
    }
}
