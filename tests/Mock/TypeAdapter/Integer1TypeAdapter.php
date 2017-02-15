<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock\TypeAdapter;

use Tebru\Gson\JsonWritable;
use Tebru\Gson\JsonReadable;
use Tebru\Gson\TypeAdapter;

/**
 * Class Integer1TypeAdapter
 *
 * @author Nate Brunette <n@tebru.net>
 */
class Integer1TypeAdapter extends TypeAdapter
{
    /**
     * Read the next value, convert it to its type and return it
     *
     * @param JsonReadable $reader
     * @return mixed
     */
    public function read(JsonReadable $reader)
    {
        return $reader->nextInteger() + 1;
    }

    /**
     * Write the value to the writer for the type
     *
     * @param JsonWritable $writer
     * @param int $value
     * @return void
     */
    public function write(JsonWritable $writer, $value): void
    {
        $writer->writeInteger($value + 1);
    }

}
