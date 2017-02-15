<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock\TypeAdapter;

use Tebru\Gson\JsonWritable;
use Tebru\Gson\JsonReadable;
use Tebru\Gson\Test\Mock\Foo;
use Tebru\Gson\TypeAdapter;

/**
 * Class FooTypeAdapter
 *
 * @author Nate Brunette <n@tebru.net>
 */
class FooTypeAdapter extends TypeAdapter
{
    /**
     * Read the next value, convert it to its type and return it
     *
     * @param JsonReadable $reader
     * @return mixed
     */
    public function read(JsonReadable $reader)
    {
        $reader->beginObject();
        $reader->endObject();

        return new Foo();
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
