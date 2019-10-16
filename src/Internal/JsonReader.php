<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal;

use Tebru\Gson\Exception\JsonSyntaxException;
use Tebru\Gson\JsonReadable;
use Tebru\Gson\JsonToken;
use Tebru\Gson\ReaderContext;

/**
 * Class JsonReader
 *
 * @author Nate Brunette <n@tebru.net>
 */
abstract class JsonReader implements JsonReadable
{
    use JsonPath;

    /**
     * A stack representing the next element to be consumed
     *
     * @var array
     */
    protected $stack = [null];

    /**
     * An array of types that map to the position in the stack
     *
     * @var array
     */
    protected $stackTypes = [JsonToken::END_DOCUMENT];

    /**
     * The current size of the stack
     *
     * @var int
     */
    protected $stackSize = 1;

    /**
     * The original payload
     *
     * @var mixed
     */
    protected $payload;

    /**
     * Runtime context to be used while reading
     *
     * @var ReaderContext
     */
    protected $context;

    /**
     * Consumes the next token and asserts it's the end of an array
     *
     * @return void
     */
    public function endArray(): void
    {
        if ($this->stackTypes[$this->stackSize - 1] !== JsonToken::END_ARRAY) {
            $this->pathIndices[$this->pathIndex]++;
            $this->assertionFailed(JsonToken::END_ARRAY);
        }

        $this->stackSize--;
        $this->pathIndex--;
    }

    /**
     * Consumes the next token and asserts it's the end of an object
     *
     * @return void
     */
    public function endObject(): void
    {
        if ($this->stackTypes[$this->stackSize - 1] !== JsonToken::END_OBJECT) {
            $this->assertionFailed(JsonToken::END_OBJECT);
        }

        $this->stackSize--;
        $this->pathNames[$this->pathIndex--] = null;
    }

    /**
     * Returns true if the array or object has another element
     *
     * If the current scope is not an array or object, this returns false
     *
     * @return bool
     */
    public function hasNext(): bool
    {
        $peek = $this->stackTypes[$this->stackSize - 1];

        return $peek !== JsonToken::END_OBJECT && $peek !== JsonToken::END_ARRAY;
    }

    /**
     * Consumes the next name and returns it
     *
     * @return string
     */
    public function nextName(): string
    {
        if ($this->stackTypes[$this->stackSize - 1] !== JsonToken::NAME) {
            $this->assertionFailed(JsonToken::NAME);
        }

        $name = (string)$this->stack[--$this->stackSize];

        $this->pathNames[$this->pathIndex] = $name;

        return $name;
    }

    /**
     * Consumes the value of the next token and asserts it's null
     *
     * @return void
     */
    public function nextNull(): void
    {
        $this->pathIndices[$this->pathIndex]++;

        if ($this->stackTypes[$this->stackSize - 1] !== JsonToken::NULL) {
            $this->assertionFailed(JsonToken::NULL);
        }

        $this->stackSize--;
    }

    /**
     * Skip the next value.  If the next value is an object or array, all children will
     * also be skipped.
     *
     * @return void
     */
    public function skipValue(): void
    {
        $this->stackSize--;

        switch ($this->stackTypes[$this->stackSize]) {
            case JsonToken::BEGIN_OBJECT:
            case JsonToken::BEGIN_ARRAY:
                $this->stackSize--;
                break;
        }

        $this->pathIndices[$this->pathIndex]--;
    }

    /**
     * Returns the type of the next token without consuming it
     *
     * @return string
     */
    public function peek(): string
    {
        return $this->stackTypes[$this->stackSize - 1];
    }

    /**
     * Returns the original json after json_decode
     *
     * @return mixed
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * Get context to be used during deserialization
     *
     * @return ReaderContext
     */
    public function getContext(): ReaderContext
    {
        return $this->context;
    }

    /**
     * Check that the next token equals the expectation
     *
     * @param string $expectedToken
     * @return void
     * @throws \Tebru\Gson\Exception\JsonSyntaxException If the next token is not the expectation
     */
    protected function assertionFailed(string $expectedToken): void
    {
        throw new JsonSyntaxException(
            \sprintf(
                'Expected "%s", but found "%s" at "%s"',
                $expectedToken,
                $this->stackTypes[$this->stackSize - 1],
                $this->getPath()
            )
        );
    }
}
