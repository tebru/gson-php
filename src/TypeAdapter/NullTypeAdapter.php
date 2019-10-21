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
 * Class NullTypeAdapter
 *
 * @author Nate Brunette <n@tebru.net>
 */
class NullTypeAdapter extends TypeAdapter
{
    /**
     * Read the next value, convert it to its type and return it
     *
     * @param null $value
     * @param ReaderContext $context
     * @return null
     */
    public function read($value, ReaderContext $context)
    {
        return null;
    }

    /**
     * Write the value to the writer for the type
     *
     * @param int|null $value
     * @param WriterContext $context
     * @return void|null
     */
    public function write($value, WriterContext $context)
    {
        return null;
    }
}
