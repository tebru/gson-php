<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal;

use ArrayIterator;
use Iterator;
use Tebru\Gson\Element\JsonArray;
use Tebru\Gson\Element\JsonElement;
use Tebru\Gson\Element\JsonNull;
use Tebru\Gson\Element\JsonObject;
use Tebru\Gson\Element\JsonPrimitive;
use Tebru\Gson\JsonToken;

/**
 * Class JsonElementReader
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class JsonElementReader extends JsonReader
{
    /**
     * Constructor
     *
     * @param JsonElement $jsonElement
     */
    public function __construct(JsonElement $jsonElement)
    {
        $this->payload = $jsonElement;
        $this->stack[$this->stackSize] = null;
        $this->stackTypes[$this->stackSize++] = JsonToken::END_DOCUMENT;
        $this->updateStack($jsonElement);
        $this->stackSize = \count($this->stack);
    }

    /**
     * Update internal stack and stack types, appending values
     *
     * @param mixed $jsonElement
     */
    private function updateStack(JsonElement $jsonElement): void
    {
        if ($jsonElement->isJsonObject()) {
            $this->stack[$this->stackSize] = null;
            $this->stackTypes[$this->stackSize++] = JsonToken::END_OBJECT;
            $this->stack[$this->stackSize] = $jsonElement;
            $this->stackTypes[$this->stackSize++] = JsonToken::BEGIN_OBJECT;
        } elseif ($jsonElement->isJsonArray()) {
            $this->stack[$this->stackSize] = null;
            $this->stackTypes[$this->stackSize++] = JsonToken::END_ARRAY;
            $this->stack[$this->stackSize] = $jsonElement;
            $this->stackTypes[$this->stackSize++] = JsonToken::BEGIN_ARRAY;
        } elseif ($jsonElement->isNumber()) {
            $this->stack[$this->stackSize] = $jsonElement;
            $this->stackTypes[$this->stackSize++] = JsonToken::NUMBER;
        } elseif ($jsonElement->isString()) {
            $this->stack[$this->stackSize] = $jsonElement;
            $this->stackTypes[$this->stackSize++] = JsonToken::STRING;
        } elseif ($jsonElement->isBoolean()) {
            $this->stack[$this->stackSize] = $jsonElement;
            $this->stackTypes[$this->stackSize++] = JsonToken::BOOLEAN;
        } else {
            $this->stack[$this->stackSize] = null;
            $this->stackTypes[$this->stackSize++] = JsonToken::NULL;
        }
    }

    /**
     * Consumes the next token and asserts it's the beginning of a new array
     *
     * @return void
     */
    public function beginArray(): void
    {
        if ($this->stackTypes[$this->stackSize - 1] !== JsonToken::BEGIN_ARRAY) {
            $this->assertionFailed(JsonToken::BEGIN_ARRAY);
        }

        $jsonArray = $this->stack[--$this->stackSize];
        $size = \count($jsonArray);
        for ($i = $size - 1; $i >= 0; $i--) {
            $this->updateStack($jsonArray[$i]);
        }
    }

    /**
     * Consumes the next token and asserts it's the beginning of a new object
     *
     * @return void
     */
    public function beginObject(): void
    {
        if ($this->stackTypes[$this->stackSize - 1] !== JsonToken::BEGIN_OBJECT) {
            $this->assertionFailed(JsonToken::BEGIN_OBJECT);
        }

        $jsonObject = $this->stack[--$this->stackSize];
        foreach ($jsonObject as $key => $value) {
            $this->updateStack($value);
            $this->stack[$this->stackSize] = $key;
            $this->stackTypes[$this->stackSize++] = JsonToken::NAME;
        }
    }

    /**
     * Consumes the value of the next token, asserts it's a boolean and returns it
     *
     * @return bool
     */
    public function nextBoolean(): bool
    {
        if ($this->stackTypes[$this->stackSize - 1] !== JsonToken::BOOLEAN) {
            $this->assertionFailed(JsonToken::BOOLEAN);
        }

        /** @var JsonPrimitive $primitive */
        $primitive = $this->stack[--$this->stackSize];

        $this->incrementPathIndex();

        return $primitive->asBoolean();
    }

    /**
     * Consumes the value of the next token, asserts it's a double and returns it
     *
     * @return double
     */
    public function nextDouble(): float
    {
        if ($this->stackTypes[$this->stackSize - 1] !== JsonToken::NUMBER) {
            $this->assertionFailed(JsonToken::NUMBER);
        }

        /** @var JsonPrimitive $primitive */
        $primitive = $this->stack[--$this->stackSize];

        $this->incrementPathIndex();

        return $primitive->asFloat();
    }

    /**
     * Consumes the value of the next token, asserts it's an int and returns it
     *
     * @return int
     */
    public function nextInteger(): int
    {
        if ($this->stackTypes[$this->stackSize - 1] !== JsonToken::NUMBER) {
            $this->assertionFailed(JsonToken::NUMBER);
        }

        /** @var JsonPrimitive $primitive */
        $primitive = $this->stack[--$this->stackSize];

        $this->incrementPathIndex();

        return $primitive->asInteger();
    }

    /**
     * Consumes the value of the next token, asserts it's a string and returns it
     *
     * @return string
     */
    public function nextString(): string
    {
        if ($this->stackTypes[$this->stackSize - 1] !== JsonToken::STRING) {
            $this->assertionFailed(JsonToken::STRING);
        }

        /** @var JsonPrimitive $primitive */
        $primitive = $this->stack[--$this->stackSize];

        $this->incrementPathIndex();

        return $primitive->asString();
    }

    /**
     * Returns an enum representing the type of the next token without consuming it
     *
     * @return string
     */
    public function peek(): string
    {
        return $this->stackTypes[$this->stackSize - 1];
    }

    /**
     * Get the current read path in json xpath format
     *
     * @return string
     */
    public function getPath(): string
    {
        $result = '$';
        foreach ($this->stack as $index => $item) {
            if ($item instanceof JsonArray && isset($this->pathIndices[$index])) {
                $result .= '['.$this->pathIndices[$index].']';
            }

            if ($item instanceof JsonObject && isset($this->pathNames[$index])) {
                $result .= '.'.$this->pathNames[$index];
            }
        }

        return $result;
    }
}
