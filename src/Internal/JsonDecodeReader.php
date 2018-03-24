<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal;

use ArrayIterator;
use stdClass;
use Tebru\Gson\Exception\JsonParseException;
use Tebru\Gson\JsonToken;

/**
 * Class JsonDecodeReader
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class JsonDecodeReader extends JsonReader
{
    /**
     * Constructor
     *
     * @param string $json
     * @throws \Tebru\Gson\Exception\JsonParseException If the json cannot be decoded
     */
    public function __construct(string $json)
    {
        $decodedJson = \json_decode($json);

        if (\json_last_error() !== JSON_ERROR_NONE) {
            throw new JsonParseException(\sprintf('Could not decode json, the error message was: "%s"', \json_last_error_msg()));
        }

        $this->payload = $decodedJson;
        $this->push($decodedJson);
    }

    /**
     * Consumes the next token and asserts it's the beginning of a new array
     *
     * @return void
     */
    public function beginArray(): void
    {
        $this->expect(JsonToken::BEGIN_ARRAY);

        $array = $this->pop();
        $this->push(new ArrayIterator($array), ArrayIterator::class);
        $this->pathIndices[$this->stackSize - 1] = 0;
    }

    /**
     * Consumes the next token and asserts it's the beginning of a new object
     *
     * @return void
     */
    public function beginObject(): void
    {
        $this->expect(JsonToken::BEGIN_OBJECT);

        $this->push(new StdClassIterator($this->pop()), StdClassIterator::class);
    }

    /**
     * Consumes the value of the next token, asserts it's a boolean and returns it
     *
     * @return bool
     */
    public function nextBoolean(): bool
    {
        $this->expect(JsonToken::BOOLEAN);

        $result = (bool)$this->pop();

        $this->incrementPathIndex();

        return $result;
    }

    /**
     * Consumes the value of the next token, asserts it's a double and returns it
     *
     * @return double
     */
    public function nextDouble(): float
    {
        $this->expect(JsonToken::NUMBER);

        $result = (float)$this->pop();

        $this->incrementPathIndex();

        return $result;
    }

    /**
     * Consumes the value of the next token, asserts it's an int and returns it
     *
     * @return int
     */
    public function nextInteger(): int
    {
        $this->expect(JsonToken::NUMBER);

        $result = (int)$this->pop();

        $this->incrementPathIndex();

        return $result;
    }

    /**
     * Consumes the value of the next token, asserts it's a string and returns it
     *
     * @return string
     */
    public function nextString(): string
    {
        $peek = $this->peek();
        if ($peek === JsonToken::NAME) {
            return $this->nextName();
        }

        $this->expect(JsonToken::STRING);

        $result = (string)$this->pop();

        $this->incrementPathIndex();

        return $result;
    }

    /**
     * Returns an enum representing the type of the next token without consuming it
     *
     * @return string
     */
    public function peek(): string
    {
        if (null !== $this->currentToken) {
            /** @noinspection PhpStrictTypeCheckingInspection */
            return $this->currentToken;
        }

        if (0 === $this->stackSize) {
            $this->currentToken = JsonToken::END_DOCUMENT;

            return $this->currentToken;
        }

        $token = null;
        $element = $this->stack[$this->stackSize - 1];

        switch ($this->stackTypes[$this->stackSize - 1]) {
            case 'array':
                $token = JsonToken::BEGIN_ARRAY;
                break;
            case 'string':
                $token = JsonToken::STRING;
                break;
            case 'double':
                $token = JsonToken::NUMBER;
                break;
            case 'integer':
                $token = JsonToken::NUMBER;
                break;
            case 'boolean':
                return JsonToken::BOOLEAN;
            case 'NULL':
                $token = JsonToken::NULL;
                break;
            case StdClassIterator::class:
                /** @var StdClassIterator $element */
                $token = $element->valid() ? JsonToken::NAME : JsonToken::END_OBJECT;
                break;
            case ArrayIterator::class:
                /** @var ArrayIterator $element */
                if ($element->valid()) {
                    $this->push($element->current());
                    $element->next();

                    $token = $this->peek();
                } else {
                    $token = JsonToken::END_ARRAY;
                }
                break;
            case 'object':
                switch (\get_class($element)) {
                    case stdClass::class:
                        $token = JsonToken::BEGIN_OBJECT;
                        break;
                }
                break;
        }

        $this->currentToken = $token;

        return $this->currentToken;
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
            if ($item instanceof ArrayIterator && isset($this->pathIndices[$index])) {
                $result .= '['.$this->pathIndices[$index].']';
            }

            if ($item instanceof StdClassIterator && isset($this->pathNames[$index])) {
                $result .= '.'.$this->pathNames[$index];
            }
        }

        return $result;
    }

    /**
     * Push an element onto the stack
     *
     * @param mixed $element
     * @param string|null $type
     */
    protected function push($element, $type = null): void
    {
        if (null === $type) {
            $type = \gettype($element);
        }

        $this->stack[$this->stackSize] = $element;
        $this->stackTypes[$this->stackSize] = $type;
        $this->stackSize++;
        $this->currentToken = null;
    }
}
