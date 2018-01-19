<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

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
    public function beginArray(): void;

    /**
     * Consumes the next token and asserts it's the end of an array
     *
     * @return void
     */
    public function endArray(): void;

    /**
     * Consumes the next token and asserts it's the beginning of a new object
     *
     * @return void
     */
    public function beginObject(): void;

    /**
     * Consumes the next token and asserts it's the end of an object
     *
     * @return void
     */
    public function endObject(): void;

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
    public function nextNull(): void;

    /**
     * Consumes the next name and returns it
     *
     * @return string
     */
    public function nextName(): string;

    /**
     * Returns an enum representing the type of the next token without consuming it
     *
     * @return string
     */
    public function peek(): string;

    /**
     * Skip the next value.  If the next value is an object or array, all children will
     * also be skipped.
     *
     * @return void
     */
    public function skipValue(): void;

    /**
     * Get the current read path in json xpath format
     *
     * @return string
     */
    public function getPath(): string;

    /**
     * Returns the original payload after json_decode
     *
     * @return mixed
     */
    public function getPayload();
}
