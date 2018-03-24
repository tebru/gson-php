<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal;

use Iterator;
use Tebru\Gson\Element\JsonElement;
use Tebru\Gson\Exception\JsonSyntaxException;
use Tebru\Gson\JsonReadable;
use Tebru\Gson\JsonToken;

/**
 * Class JsonReader
 *
 * @author Nate Brunette <n@tebru.net>
 */
abstract class JsonReader implements JsonReadable
{
    /**
     * A stack representing the next element to be consumed
     *
     * @var array
     */
    protected $stack = [];

    /**
     * An array of types that map to the position in the stack
     *
     * @var array
     */
    protected $stackTypes = [];

    /**
     * The current size of the stack
     *
     * @var int
     */
    protected $stackSize = 0;

    /**
     * An array of path names that correspond to the current stack
     *
     * @var array
     */
    protected $pathNames = [];

    /**
     * An array of path indices that correspond to the current stack. This array could contain invalid
     * values at indexes outside the current stack. It could also contain incorrect values at indexes
     * where a path name is used. Data should only be fetched by referencing the current position in the stack.
     *
     * @var array
     */
    protected $pathIndices = [];

    /**
     * A cache of the current [@see JsonToken].  This should get nulled out
     * whenever a new token should be returned with the subsequent call
     * to [@see JsonDecodeReader::peek]
     *
     * @var int|null
     */
    protected $currentToken;

    /**
     * The original payload
     *
     * @var mixed
     */
    protected $payload;

    /**
     * Returns an enum representing the type of the next token without consuming it
     *
     * @return string
     */
    abstract public function peek(): string;

    /**
     * Get the current read path in json xpath format
     *
     * @return string
     */
    abstract public function getPath(): string;

    /**
     * Push an element onto the stack
     *
     * @param JsonElement|Iterator $element
     * @param string|null $type
     * @return void
     */
    abstract protected function push($element, $type = null): void;

    /**
     * Consumes the next token and asserts it's the end of an array
     *
     * @return void
     */
    public function endArray(): void
    {
        $this->expect(JsonToken::END_ARRAY);

        $this->pop();
        $this->incrementPathIndex();
    }

    /**
     * Consumes the next token and asserts it's the end of an object
     *
     * @return void
     */
    public function endObject(): void
    {
        $this->expect(JsonToken::END_OBJECT);

        $this->pop();
        $this->incrementPathIndex();
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
        $peek = $this->peek();

        return $peek !== JsonToken::END_OBJECT && $peek !== JsonToken::END_ARRAY;
    }

    /**
     * Consumes the next name and returns it
     *
     * @return string
     */
    public function nextName(): string
    {
        $this->expect(JsonToken::NAME);

        /** @var Iterator $iterator */
        $iterator = $this->stack[$this->stackSize - 1];
        $key = $iterator->key();
        $value = $iterator->current();
        $iterator->next();

        $this->pathNames[$this->stackSize - 1] = $key;

        $this->push($value);

        return (string)$key;
    }

    /**
     * Consumes the value of the next token and asserts it's null
     *
     * @return void
     */
    public function nextNull(): void
    {
        $this->expect(JsonToken::NULL);

        $this->pop();

        $this->incrementPathIndex();
    }

    /**
     * Skip the next value.  If the next value is an object or array, all children will
     * also be skipped.
     *
     * @return void
     */
    public function skipValue(): void
    {
        $this->pop();
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
     * Pop the last element off the stack and return it
     *
     * @return JsonElement|Iterator|mixed
     */
    protected function pop()
    {
        $this->stackSize--;
        \array_pop($this->stackTypes);
        $this->currentToken = null;

        return \array_pop($this->stack);
    }

    /**
     * Check that the next token equals the expectation
     *
     * @param string $expectedToken
     * @return void
     * @throws \Tebru\Gson\Exception\JsonSyntaxException If the next token is not the expectation
     */
    protected function expect(string $expectedToken): void
    {
        if ($this->peek() === $expectedToken) {
            return;
        }

        // increment the path index because exceptions are thrown before this value is increased. We
        // want to display the current index that has a problem.
        $this->incrementPathIndex();

        throw new JsonSyntaxException(
            \sprintf('Expected "%s", but found "%s" at "%s"', $expectedToken, $this->peek(), $this->getPath())
        );
    }

    /**
     * Increment the path index. This should be called any time a new value is parsed.
     */
    protected function incrementPathIndex(): void
    {
        $index = $this->stackSize - 1;
        if ($index >= 0) {
            if (!isset($this->pathIndices[$index])) {
                $this->pathIndices[$index] = 0;
            }
            $this->pathIndices[$index]++;
        }
    }
}
