<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal\TypeAdapter;

use Tebru\Gson\JsonReadable;
use Tebru\Gson\JsonWritable;
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
            $reader->nextNull();
            return null;
        }

        return $reader->nextDouble();
    }

    /**
     * Write the value to the writer for the type
     *
     * @param JsonWritable $writer
     * @param float $value
     * @return void
     */
    public function write(JsonWritable $writer, $value): void
    {
        if (null === $value) {
            $writer->writeNull();

            return;
        }

        $writer->writeFloat((float)$value);
    }
}
