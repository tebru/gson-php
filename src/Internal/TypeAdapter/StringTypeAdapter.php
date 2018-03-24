<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal\TypeAdapter;

use Tebru\Gson\JsonWritable;
use Tebru\Gson\JsonReadable;
use Tebru\Gson\JsonToken;
use Tebru\Gson\TypeAdapter;

/**
 * Class StringTypeAdapter
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class StringTypeAdapter extends TypeAdapter
{
    /**
     * Read the next value, convert it to its type and return it
     *
     * @param JsonReadable $reader
     * @return string|null
     */
    public function read(JsonReadable $reader): ?string
    {
        if ($reader->peek() === JsonToken::NULL) {
            $reader->nextNull();
            return null;
        }

        return $reader->nextString();
    }

    /**
     * Write the value to the writer for the type
     *
     * @param JsonWritable $writer
     * @param string $value
     * @return void
     */
    public function write(JsonWritable $writer, $value): void
    {
        if (null === $value) {
            $writer->writeNull();

            return;
        }

        $writer->writeString((string)$value);
    }
}
