<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock\TypeAdapter;

use Tebru\Gson\JsonReadable;
use Tebru\Gson\JsonWritable;
use Tebru\Gson\Test\Mock\GsonObjectMock;
use Tebru\Gson\TypeAdapter;

/**
 * Class GsonObjectMockTypeAdapterMock
 *
 * @author Nate Brunette <n@tebru.net>
 */
class GsonObjectMockTypeAdapterMock extends TypeAdapter
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
        $reader->nextName();
        $gsonObjectMock = new GsonObjectMock($reader->nextString());
        $reader->endObject();

        return $gsonObjectMock;
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
        // TODO: Implement write() method.
    }
}
