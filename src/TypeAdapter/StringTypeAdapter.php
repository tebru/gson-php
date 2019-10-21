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
 * Class StringTypeAdapter
 *
 * @author Nate Brunette <n@tebru.net>
 */
class StringTypeAdapter extends TypeAdapter
{
    /**
     * Read the next value, convert it to its type and return it
     *
     * @param string|null $value
     * @param ReaderContext $context
     * @return string|null
     */
    public function read($value, ReaderContext $context): ?string
    {
        return $value === null ? null : (string)$value;
    }

    /**
     * Write the value to the writer for the type
     *
     * @param string|null $value
     * @param WriterContext $context
     * @return string|null
     */
    public function write($value, WriterContext $context): ?string
    {
        return $value === null ? null : (string)$value;
    }
}
