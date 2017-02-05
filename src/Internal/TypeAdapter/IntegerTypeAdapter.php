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
 * Class IntegerTypeAdapter
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class IntegerTypeAdapter extends TypeAdapter
{
    /**
     * Read the next value, convert it to its type and return it
     *
     * @param JsonReadable $reader
     * @return int|null
     */
    public function read(JsonReadable $reader): ?int
    {
        if ($reader->peek() === JsonToken::NULL) {
            return $reader->nextNull();
        }

        return $reader->nextInteger();
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
