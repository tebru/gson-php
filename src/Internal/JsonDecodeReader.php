<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal;

use Tebru\Gson\Exception\JsonParseException;
use Tebru\Gson\ReaderContext;
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
     * @param ReaderContext $context
     * @throws \Tebru\Gson\Exception\JsonParseException If the json cannot be decoded
     */
    public function __construct(string $json, ReaderContext $context)
    {
        $decodedJson = \json_decode($json);

        if (\json_last_error() !== JSON_ERROR_NONE) {
            throw new JsonParseException(\sprintf('Could not decode json, the error message was: "%s"', \json_last_error_msg()));
        }

        $this->payload = $decodedJson;
        $this->context = $context;
        $this->updateStack($decodedJson);
    }

    /**
     * Update internal stack and stack types, appending values
     *
     * @param mixed $decodedJson
     */
    private function updateStack($decodedJson): void
    {
        $type = \gettype($decodedJson);

        switch ($type) {
            case 'object':
                $this->stack[$this->stackSize] = null;
                $this->stackTypes[$this->stackSize++] = JsonToken::END_OBJECT;
                $this->stack[$this->stackSize] = $decodedJson;
                $this->stackTypes[$this->stackSize++] = JsonToken::BEGIN_OBJECT;
                break;
            case 'array':
                $this->stack[$this->stackSize] = null;
                $this->stackTypes[$this->stackSize++] = JsonToken::END_ARRAY;
                $this->stack[$this->stackSize] = $decodedJson;
                $this->stackTypes[$this->stackSize++] = JsonToken::BEGIN_ARRAY;
                break;
            case 'string':
                $this->stack[$this->stackSize] = $decodedJson;
                $this->stackTypes[$this->stackSize++] = JsonToken::STRING;
                break;
            case 'boolean':
                $this->stack[$this->stackSize] = $decodedJson;
                $this->stackTypes[$this->stackSize++] = JsonToken::BOOLEAN;
                break;
            case 'integer':
            case 'double':
                $this->stack[$this->stackSize] = $decodedJson;
                $this->stackTypes[$this->stackSize++] = JsonToken::NUMBER;
                break;
            default:
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

        $array = $this->stack[--$this->stackSize];
        $size = \count($array);
        for ($i = $size - 1; $i >= 0; $i--) {
            $this->updateStack($array[$i]);
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

        $vars = \array_reverse(\get_object_vars($this->stack[--$this->stackSize]), true);
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

        return (bool)$this->stack[--$this->stackSize];
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

        return (float)$this->stack[--$this->stackSize];
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

        return (int)$this->stack[--$this->stackSize];
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

        return (string)$this->stack[--$this->stackSize];
    }
}
