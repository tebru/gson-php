<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal;

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
        $this->stack[$this->stackSize] = null;
        $this->stackTypes[$this->stackSize++] = JsonToken::END_DOCUMENT;
        $this->updateStack($decodedJson);
        $this->stackSize = \count($this->stack);
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
        if ($this->stackTypes[$this->stackSize - 1] !== JsonToken::BEGIN_OBJECT) {
            $this->assertionFailed(JsonToken::BEGIN_OBJECT);
        }

        $vars = \get_object_vars($this->stack[--$this->stackSize]);
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
        if ($this->stackTypes[$this->stackSize - 1] !== JsonToken::BOOLEAN) {
            $this->assertionFailed(JsonToken::BOOLEAN);
        }

        $result = (bool)$this->stack[--$this->stackSize];

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
        if ($this->stackTypes[$this->stackSize - 1] !== JsonToken::NUMBER) {
            $this->assertionFailed(JsonToken::NUMBER);
        }

        $result = (float)$this->stack[--$this->stackSize];

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
        if ($this->stackTypes[$this->stackSize - 1] !== JsonToken::NUMBER) {
            $this->assertionFailed(JsonToken::NUMBER);
        }

        $result = (int)$this->stack[--$this->stackSize];

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
        if ($this->stackTypes[$this->stackSize - 1] !== JsonToken::STRING) {
            $this->assertionFailed(JsonToken::STRING);
        }

        $result = (string)$this->stack[--$this->stackSize];

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
        return $this->stackTypes[$this->stackSize - 1];
    }

    /**
     * Get the current read path in json xpath format
     *
     * @return string
     */
    public function getPath(): string
    {
        $result[] = '$';
        $resultIndex = 1;
        $arrayIndex = 0;

        for ($index = 0; $index < $this->stackSize; $index++) {
            switch ($this->stackTypes[$index]) {
                case JsonToken::END_OBJECT:
                    $result[$resultIndex] = '.';
                    $resultIndex++;
                    break;
                case JsonToken::END_ARRAY:
                    $resultIndex++;
                    $arrayIndex = 0;
                    break;
                case JsonToken::BEGIN_OBJECT:
                    $resultIndex--;
                    break;
                case JsonToken::BEGIN_ARRAY:
                    $resultIndex--;
                    break;
                case JsonToken::NAME:
                    $result[$resultIndex] = $this->stack[$index];
                    $resultIndex++;
                    break;
                default:
                    $arrayIndex++;

            }
            $item = $this->stack[$index];
            if (\is_array($item) && isset($this->pathIndices[$index])) {
                $result .= '['.$this->pathIndices[$index].']';
            }

            if (\is_object($item) && isset($this->pathNames[$index])) {
                $result .= '.'.$this->pathNames[$index];
            }
        }

        return $result;
    }
}
