<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal;

use Tebru\Gson\Element\JsonArray;
use Tebru\Gson\Element\JsonElement;
use Tebru\Gson\Element\JsonObject;
use Tebru\Gson\Element\JsonPrimitive;
use Tebru\Gson\JsonToken;
use Tebru\Gson\ReaderContext;

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
     * @param ReaderContext $context
     */
    public function __construct(JsonElement $jsonElement, ReaderContext $context)
    {
        $this->payload = $jsonElement;
        $this->context = $context;
        $this->updateStack($jsonElement);
    }

    /**
     * Update internal stack and stack types, appending values
     *
     * @param JsonElement $jsonElement
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
        $this->pathIndices[$this->pathIndex++]++;
        $this->pathIndices[$this->pathIndex] = -1;

        if ($this->stackTypes[$this->stackSize - 1] !== JsonToken::BEGIN_ARRAY) {
            $this->assertionFailed(JsonToken::BEGIN_ARRAY);
        }

        /** @var JsonArray $jsonArray */
        $jsonArray = $this->stack[--$this->stackSize];
        $size = \count($jsonArray);
        for ($i = $size - 1; $i >= 0; $i--) {
            $this->updateStack($jsonArray->get($i));
        }
    }

    /**
     * Consumes the next token and asserts it's the beginning of a new object
     *
     * @return void
     */
    public function beginObject(): void
    {
        $this->pathIndices[$this->pathIndex++]++;
        $this->pathIndices[$this->pathIndex] = -1;

        if ($this->stackTypes[$this->stackSize - 1] !== JsonToken::BEGIN_OBJECT) {
            $this->assertionFailed(JsonToken::BEGIN_OBJECT);
        }

        /** @var JsonObject $jsonObject */
        $jsonObject = $this->stack[--$this->stackSize];
        $vars = \array_reverse($jsonObject->getProperties(), true);
        foreach ($vars as $key => $value) {
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
        $this->pathIndices[$this->pathIndex]++;

        if ($this->stackTypes[$this->stackSize - 1] !== JsonToken::BOOLEAN) {
            $this->assertionFailed(JsonToken::BOOLEAN);
        }

        /** @var JsonPrimitive $primitive */
        $primitive = $this->stack[--$this->stackSize];

        return $primitive->asBoolean();
    }

    /**
     * Consumes the value of the next token, asserts it's a double and returns it
     *
     * @return double
     */
    public function nextDouble(): float
    {
        $this->pathIndices[$this->pathIndex]++;

        if ($this->stackTypes[$this->stackSize - 1] !== JsonToken::NUMBER) {
            $this->assertionFailed(JsonToken::NUMBER);
        }

        /** @var JsonPrimitive $primitive */
        $primitive = $this->stack[--$this->stackSize];

        return $primitive->asFloat();
    }

    /**
     * Consumes the value of the next token, asserts it's an int and returns it
     *
     * @return int
     */
    public function nextInteger(): int
    {
        $this->pathIndices[$this->pathIndex]++;

        if ($this->stackTypes[$this->stackSize - 1] !== JsonToken::NUMBER) {
            $this->assertionFailed(JsonToken::NUMBER);
        }

        /** @var JsonPrimitive $primitive */
        $primitive = $this->stack[--$this->stackSize];

        return $primitive->asInteger();
    }

    /**
     * Consumes the value of the next token, asserts it's a string and returns it
     *
     * @return string
     */
    public function nextString(): string
    {
        $this->pathIndices[$this->pathIndex]++;

        if ($this->stackTypes[$this->stackSize - 1] !== JsonToken::STRING) {
            if ($this->stackTypes[$this->stackSize - 1] === JsonToken::NAME) {
                return $this->nextName();
            }

            $this->assertionFailed(JsonToken::STRING);
        }

        /** @var JsonPrimitive $primitive */
        $primitive = $this->stack[--$this->stackSize];

        return $primitive->asString();
    }
}
