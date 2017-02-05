<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal\TypeAdapter;

use Tebru\Gson\JsonReadable;
use Tebru\Gson\Internal\JsonWritable;
use Tebru\Gson\JsonToken;
use Tebru\Gson\TypeAdapter;

/**
 * Class FloatTypeAdapter
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class FloatTypeAdapter extends TypeAdapter
{
    /**
     * Read the next value, convert it to its type and return it
     *
     * @param JsonReadable $reader
     * @return float|null
     */
    public function read(JsonReadable $reader): ?float
    {
        if ($reader->peek() === JsonToken::NULL) {
            return $reader->nextNull();
        }

        return $reader->nextDouble();
    }

    /**
     * Write the value to the writer for the type
     *
     * @param JsonWritable $writer
     * @param mixed $value
     * @return void
     */
    public function write(JsonWritable $writer, $value): void
    {
    }
}
