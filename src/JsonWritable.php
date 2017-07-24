<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson;

/**
 * Interface JsonWritable
 *
 * @author Nate Brunette <n@tebru.net>
 */
interface JsonWritable
{
    /**
     * Begin writing array
     *
     * @return JsonWritable
     */
    public function beginArray(): JsonWritable;

    /**
     * End writing array
     *
     * @return JsonWritable
     */
    public function endArray(): JsonWritable;

    /**
     * Begin writing object
     *
     * @return JsonWritable
     */
    public function beginObject(): JsonWritable;

    /**
     * End writing object
     *
     * @return JsonWritable
     */
    public function endObject(): JsonWritable;

    /**
     * Writes a property name
     *
     * @param string $name
     * @return JsonWritable
     */
    public function name(string $name): JsonWritable;

    /**
     * Write an integer value
     *
     * @param int $value
     * @return JsonWritable
     */
    public function writeInteger(int $value): JsonWritable;

    /**
     * Write a float value
     *
     * @param float $value
     * @return JsonWritable
     */
    public function writeFloat(float $value): JsonWritable;

    /**
     * Write a string value
     *
     * @param string $value
     * @return JsonWritable
     */
    public function writeString(string $value): JsonWritable;

    /**
     * Write a boolean value
     *
     * @param boolean $value
     * @return JsonWritable
     */
    public function writeBoolean(bool $value): JsonWritable;

    /**
     * Write a null value if we are serializing nulls, otherwise
     * skip the value.  If this is a property value, that property
     * should be skipped as well.
     *
     * @return JsonWritable
     */
    public function writeNull(): JsonWritable;

    /**
     * Sets whether nulls are serialized
     *
     * @param bool $serializeNull
     * @return void
     */
    public function setSerializeNull(bool $serializeNull): void;
}
