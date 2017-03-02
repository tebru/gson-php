<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal;

use ArrayIterator;
use SebastianBergmann\CodeCoverage\Node\Iterator;
use Tebru\Gson\Element\JsonArray;
use Tebru\Gson\Element\JsonElement;
use Tebru\Gson\Element\JsonNull;
use Tebru\Gson\Element\JsonObject;
use Tebru\Gson\Element\JsonPrimitive;
use Tebru\Gson\Exception\UnexpectedJsonTokenException;
use Tebru\Gson\JsonReadable;
use Tebru\Gson\JsonToken;

/**
 * Class JsonElementReader
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class JsonElementReader implements JsonReadable
{
    /**
     * A stack representing the next element to be consumed
     *
     * @var array
     */
    private $stack = [];

    /**
     * An array of types that map to the position in the stack
     *
     * @var array
     */
    private $stackTypes = [];

    /**
     * The current size of the stack
     *
     * @var int
     */
    private $stackSize = 0;

    /**
     * A cache of the current [@see JsonToken].  This should get nulled out
     * whenever a new token should be returned with the subsequent call
     * to [@see JsonDecodeReader::peek]
     *
     * @var
     */
    private $currentToken;

    /**
     * Constructor
     *
     * @param JsonElement $jsonElement
     */
    public function __construct(JsonElement $jsonElement)
    {
        $this->push($jsonElement);
    }

    /**
     * Consumes the next token and asserts it's the beginning of a new array
     *
     * @return void
     * @throws \Tebru\Gson\Exception\UnexpectedJsonTokenException If the next token is not BEGIN_ARRAY
     */
    public function beginArray(): void
    {
        if ($this->peek() !== JsonToken::BEGIN_ARRAY) {
            throw new UnexpectedJsonTokenException(
                sprintf('Expected "%s", but found "%s"', JsonToken::BEGIN_ARRAY, $this->peek())
            );
        }

        /** @var JsonArray $jsonArray */
        $jsonArray = $this->pop();
        $this->push($jsonArray->getIterator(), ArrayIterator::class);
    }

    /**
     * Consumes the next token and asserts it's the end of an array
     *
     * @return void
     * @throws \Tebru\Gson\Exception\UnexpectedJsonTokenException If the next token is not END_ARRAY
     */
    public function endArray(): void
    {
        if ($this->peek() !== JsonToken::END_ARRAY) {
            throw new UnexpectedJsonTokenException(
                sprintf('Expected "%s", but found "%s"', JsonToken::END_ARRAY, $this->peek())
            );
        }

        $this->pop();
    }

    /**
     * Consumes the next token and asserts it's the beginning of a new object
     *
     * @return void
     * @throws \Tebru\Gson\Exception\UnexpectedJsonTokenException If the next token is not BEGIN_OBJECT
     */
    public function beginObject(): void
    {
        if ($this->peek() !== JsonToken::BEGIN_OBJECT) {
            throw new UnexpectedJsonTokenException(
                sprintf('Expected "%s", but found "%s"', JsonToken::BEGIN_OBJECT, $this->peek())
            );
        }

        $this->push(new JsonObjectIterator($this->pop()->asJsonObject()), JsonObjectIterator::class);
    }

    /**
     * Consumes the next token and asserts it's the end of an object
     *
     * @return void
     * @throws \Tebru\Gson\Exception\UnexpectedJsonTokenException If the next token is not END_OBJECT
     */
    public function endObject(): void
    {
        if ($this->peek() !== JsonToken::END_OBJECT) {
            throw new UnexpectedJsonTokenException(
                sprintf('Expected "%s", but found "%s"', JsonToken::END_OBJECT, $this->peek())
            );
        }

        $this->pop();
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
     * Consumes the value of the next token, asserts it's a boolean and returns it
     *
     * @return bool
     * @throws \Tebru\Gson\Exception\UnexpectedJsonTokenException If the next token is not BOOLEAN
     */
    public function nextBoolean(): bool
    {
        if ($this->peek() !== JsonToken::BOOLEAN) {
            throw new UnexpectedJsonTokenException(
                sprintf('Expected "%s", but found "%s"', JsonToken::BOOLEAN, $this->peek())
            );
        }

        return $this->pop()->asBoolean();
    }

    /**
     * Consumes the value of the next token, asserts it's a double and returns it
     *
     * @return double
     * @throws \Tebru\Gson\Exception\UnexpectedJsonTokenException If the next token is not NUMBER
     */
    public function nextDouble(): float
    {
        if ($this->peek() !== JsonToken::NUMBER) {
            throw new UnexpectedJsonTokenException(
                sprintf('Expected "%s", but found "%s"', JsonToken::NUMBER, $this->peek())
            );
        }

        return $this->pop()->asFloat();
    }

    /**
     * Consumes the value of the next token, asserts it's an int and returns it
     *
     * @return int
     * @throws \Tebru\Gson\Exception\UnexpectedJsonTokenException If the next token is not NUMBER
     */
    public function nextInteger(): int
    {
        if ($this->peek() !== JsonToken::NUMBER) {
            throw new UnexpectedJsonTokenException(
                sprintf('Expected "%s", but found "%s"', JsonToken::NUMBER, $this->peek())
            );
        }

        return $this->pop()->asInteger();
    }

    /**
     * Consumes the value of the next token, asserts it's a string and returns it
     *
     * @return string
     * @throws \Tebru\Gson\Exception\UnexpectedJsonTokenException If the next token is not NAME or STRING
     */
    public function nextString(): string
    {
        $peek = $this->peek();
        if ($peek === JsonToken::NAME) {
            return $this->nextName();
        }

        if ($peek !== JsonToken::STRING) {
            throw new UnexpectedJsonTokenException(
                sprintf('Expected "%s", but found "%s"', JsonToken::STRING, $this->peek())
            );
        }

        return $this->pop()->asString();
    }

    /**
     * Consumes the value of the next token and asserts it's null
     *
     * @return null
     * @throws \Tebru\Gson\Exception\UnexpectedJsonTokenException If the next token is not NAME or NULL
     */
    public function nextNull()
    {
        if ($this->peek() !== JsonToken::NULL) {
            throw new UnexpectedJsonTokenException(
                sprintf('Expected "%s", but found "%s"', JsonToken::NULL, $this->peek())
            );
        }

        $this->pop();

        return null;
    }

    /**
     * Consumes the next name and returns it
     *
     * @return string
     * @throws \Tebru\Gson\Exception\UnexpectedJsonTokenException If the next token is not NAME
     */
    public function nextName(): string
    {
        if ($this->peek() !== JsonToken::NAME) {
            throw new UnexpectedJsonTokenException(
                sprintf('Expected "%s", but found "%s"', JsonToken::NAME, $this->peek())
            );
        }

        /** @var JsonObjectIterator $iterator */
        $iterator = $this->stack[$this->stackSize - 1];
        $key = $iterator->key();
        $value = $iterator->current();
        $iterator->next();

        $this->push($value);

        return $key;
    }

    /**
     * Returns an enum representing the type of the next token without consuming it
     *
     * @return string
     */
    public function peek(): string
    {
        if (null !== $this->currentToken) {
            return $this->currentToken;
        }

        if (0 === $this->stackSize) {
            $this->currentToken = JsonToken::END_DOCUMENT;

            return $this->currentToken;
        }

        $token = null;
        $element = $this->stack[$this->stackSize - 1];

        switch ($this->stackTypes[$this->stackSize - 1]) {
            case JsonArray::class:
                $token = JsonToken::BEGIN_ARRAY;
                break;
            case JsonNull::class:
                $token = JsonToken::NULL;
                break;
            case JsonObject::class:
                $token = JsonToken::BEGIN_OBJECT;
                break;
            case JsonPrimitive::class:
                if ($element->isString()) {
                    $token = JsonToken::STRING;
                } elseif ($element->isBoolean()) {
                    $token = JsonToken::BOOLEAN;
                } elseif ($element->isNumber()) {
                    $token = JsonToken::NUMBER;
                }

                break;
            case JsonObjectIterator::class:
                $token = $element->valid() ? JsonToken::NAME : JsonToken::END_OBJECT;

                break;
            case ArrayIterator::class:
                if ($element->valid()) {
                    $this->push($element->current());
                    $element->next();

                    $token = $this->peek();
                } else {
                    $token = JsonToken::END_ARRAY;
                }

                break;
        }

        $this->currentToken = $token;

        return $this->currentToken;
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
     * Push an element onto the stack
     *
     * @param JsonElement|Iterator $element
     * @param string $type
     */
    private function push($element, $type = null): void
    {
        if (null === $type) {
            $type = get_class($element);
        }

        $this->stack[$this->stackSize] = $element;
        $this->stackTypes[$this->stackSize] = $type;
        $this->stackSize++;
        $this->currentToken = null;
    }

    /**
     * Pop the last element off the stack and return it
     *
     * @return JsonElement|Iterator
     */
    private function pop()
    {
        $this->stackSize--;
        array_pop($this->stackTypes);
        $this->currentToken = null;

        return array_pop($this->stack);
    }
}
