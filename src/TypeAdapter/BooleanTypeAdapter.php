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
 * Class BooleanTypeAdapter
 *
 * @author Nate Brunette <n@tebru.net>
 */
class BooleanTypeAdapter extends TypeAdapter
{
    /**
     * Read the next value, convert it to its type and return it
     *
     * @param bool|null $value
     * @param ReaderContext $context
     * @return bool|null
     */
    public function read($value, ReaderContext $context): ?bool
    {
        return $value === null ? null : (bool)$value;
    }

    /**
     * Write the value to the writer for the type
     *
     * @param boolean|null $value
     * @param WriterContext $context
     * @return bool|null
     */
    public function write($value, WriterContext $context): ?bool
    {
        return $value === null ? null : (bool)$value;
    }
}
