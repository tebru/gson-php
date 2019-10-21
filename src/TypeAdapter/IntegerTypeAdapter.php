<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\TypeAdapter;

use Tebru\Gson\Context\ReaderContext;
use Tebru\Gson\TypeAdapter;
use Tebru\Gson\Context\WriterContext;

/**
 * Class IntegerTypeAdapter
 *
 * @author Nate Brunette <n@tebru.net>
 */
class IntegerTypeAdapter extends TypeAdapter
{
    /**
     * Read the next value, convert it to its type and return it
     *
     * @param int|null $value
     * @param ReaderContext $context
     * @return int|null
     */
    public function read($value, ReaderContext $context): ?int
    {
        return $value === null ? null : (int)$value;
    }

    /**
     * Write the value to the writer for the type
     *
     * @param int|null $value
     * @param WriterContext $context
     * @return int|null
     */
    public function write($value, WriterContext $context): ?int
    {
        return $value === null ? null : (int)$value;
    }
}
