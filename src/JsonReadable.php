<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson;

/**
 * Interface JsonReadable
 *
 * An api to sequentially step through a json structure
 *
 * @author Nate Brunette <n@tebru.net>
 */
interface JsonReadable
{
    /**
     * Consumes the next token and asserts it's the beginning of a new array
     *
     * @return void
     */
    public function beginArray();

    /**
     * Consumes the next token and asserts it's the end of an array
     *
     * @return void
     */
    public function endArray();

    /**
     * Consumes the next token and asserts it's the beginning of a new object
     *
     * @return void
     */
    public function beginObject();

    /**
     * Consumes the next token and asserts it's the end of an object
     *
     * @return void
     */
    public function endObject();

    /**
     * Returns true if the array or object has another element
     *
     * If the current scope is not an array or object, this returns false
     *
     * @return bool
     */
    public function hasNext(): bool;

    /**
     * Consumes the value of the next token, asserts it's a boolean and returns it
     *
     * @return bool
     */
    public function nextBoolean(): bool;

    /**
     * Consumes the value of the next token, asserts it's a double and returns it
     *
     * @return double
     */
    public function nextDouble(): float;

    /**
     * Consumes the value of the next token, asserts it's an int and returns it
     *
     * @return int
     */
    public function nextInteger(): int;

    /**
     * Consumes the value of the next token, asserts it's a string and returns it
     *
     * @return string
     */
    public function nextString(): string;

    /**
     * Consumes the value of the next token and asserts it's null
     *
     * @return void
     */
    public function nextNull();

    /**
     * Consumes the next name and returns it
     *
     * @return string
     */
    public function nextName(): string;

    /**
     * Returns an enum representing the type of the next token without consuming it
     *
     * @return JsonToken
     */
    public function peek(): JsonToken;

    /**
     * Skip the next value.  If the next value is an object or array, all children will
     * also be skipped.
     *
     * @return void
     */
    public function skipValue();
}
