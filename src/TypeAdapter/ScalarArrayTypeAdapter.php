<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\TypeAdapter;

use Tebru\Gson\Context\ReaderContext;
use Tebru\Gson\Context\WriterContext;
use Tebru\Gson\TypeAdapter;

/**
 * Class ScalarArrayTypeAdapter
 *
 * @author Nate Brunette <n@tebru.net>
 */
class ScalarArrayTypeAdapter extends TypeAdapter
{
    /**
     * Read the next value, convert it to its type and return it
     *
     * @param array|null $value
     * @param ReaderContext $context
     * @return array|null
     */
    public function read($value, ReaderContext $context): ?array
    {
        return $value === null ? null : (array)$value;
    }

    /**
     * Write the value to the writer for the type
     *
     * @param array|null $value
     * @param WriterContext $context
     * @return array|null
     */
    public function write($value, WriterContext $context): ?array
    {
        return $value === null ? null : (array)$value;
    }
}
