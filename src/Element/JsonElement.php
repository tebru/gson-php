<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Element;

use JsonSerializable;
use Tebru\Gson\Exception\UnsupportedOperationException;

/**
 * Class JsonElement
 *
 * Base class for json element types
 *
 * @author Nate Brunette <n@tebru.net>
 */
abstract class JsonElement implements JsonSerializable
{
    /**
     * Returns from if the element is an instance of [@see JsonObject]
     *
     * @return bool
     */
    public function isJsonObject(): bool
    {
        return $this instanceof JsonObject;
    }

    /**
     * Returns from if the element is an instance of [@see JsonArray]
     *
     * @return bool
     */
    public function isJsonArray(): bool
    {
        return $this instanceof JsonArray;
    }

    /**
     * Returns from if the element is an instance of [@see JsonPrimitive]
     *
     * @return bool
     */
    public function isJsonPrimitive(): bool
    {
        return $this instanceof JsonPrimitive;
    }

    /**
     * Returns from if the element is an instance of [@see JsonNull]
     *
     * @return bool
     */
    public function isJsonNull(): bool
    {
        return $this instanceof JsonNull;
    }

    /**
     * Returns true if the value is a string
     *
     * @return bool
     */
    public function isString(): bool
    {
        return false;
    }

    /**
     * Returns true if the value is an integer
     *
     * @return bool
     */
    public function isInteger(): bool
    {
        return false;
    }

    /**
     * Returns true if the value is a float
     *
     * @return bool
     */
    public function isFloat(): bool
    {
        return false;
    }

    /**
     * Returns true if the value is an integer or float
     *
     * @return bool
     */
    public function isNumber(): bool
    {
        return false;
    }

    /**
     * Returns true if the value is a boolean
     *
     * @return bool
     */
    public function isBoolean(): bool
    {
        return false;
    }

    /**
     * Cast the value to a string
     *
     * @return string
     * @throws \Tebru\Gson\Exception\UnsupportedOperationException
     */
    public function asString(): string
    {
        throw new UnsupportedOperationException(\sprintf('This method "asString" is not supported on "%s"', static::class));
    }

    /**
     * Cast the value to an integer
     *
     * @return int
     * @throws \Tebru\Gson\Exception\UnsupportedOperationException
     */
    public function asInteger(): int
    {
        throw new UnsupportedOperationException(\sprintf('This method "asInteger" is not supported on "%s"', static::class));
    }

    /**
     * Cast the value to a float
     *
     * @return float
     * @throws \Tebru\Gson\Exception\UnsupportedOperationException
     */
    public function asFloat(): float
    {
        throw new UnsupportedOperationException(\sprintf('This method "asFloat" is not supported on "%s"', static::class));
    }

    /**
     * Cast the value to a boolean
     *
     * @return bool
     * @throws \Tebru\Gson\Exception\UnsupportedOperationException
     */
    public function asBoolean(): bool
    {
        throw new UnsupportedOperationException(\sprintf('This method "asBoolean" is not supported on "%s"', static::class));
    }

    /**
     * Get the value as a JsonObject
     *
     * @return JsonObject
     * @throws \Tebru\Gson\Exception\UnsupportedOperationException
     */
    public function asJsonObject(): JsonObject
    {
        throw new UnsupportedOperationException(\sprintf('This method "asJsonObject" is not supported on "%s"', static::class));
    }

    /**
     * Get the value as a JsonArray
     *
     * @return JsonArray
     * @throws \Tebru\Gson\Exception\UnsupportedOperationException
     */
    public function asJsonArray(): JsonArray
    {
        throw new UnsupportedOperationException(\sprintf('This method "asJsonArray" is not supported on "%s"', static::class));
    }

    /**
     * Get the current value
     *
     * @return mixed
     * @throws \Tebru\Gson\Exception\UnsupportedOperationException
     */
    public function getValue()
    {
        throw new UnsupportedOperationException(\sprintf('This method "getValue" is not supported on "%s"', static::class));
    }
}
