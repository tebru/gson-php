<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\TypeAdapter;

use Tebru\Gson\JsonReadable;
use Tebru\Gson\JsonWritable;
use Tebru\Gson\TypeAdapter;

/**
 * Class NullTypeAdapter
 *
 * @author Nate Brunette <n@tebru.net>
 */
class NullTypeAdapter extends TypeAdapter
{
    /**
     * Read the next value, convert it to its type and return it
     *
     * @param JsonReadable $reader
     * @return void|null
     */
    public function read(JsonReadable $reader): void
    {
        $reader->nextNull();
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
        $writer->writeNull();
    }
}
