<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson;

use Tebru\Gson\Context\ReaderContext;
use Tebru\Gson\Context\WriterContext;

/**
 * Class TypeAdapter
 *
 * Create custom TypeAdapters by extending this class.  This provides a low level
 * alternative to creating JsonSerializers or JsonDeserializers.
 *
 * @author Nate Brunette <n@tebru.net>
 */
abstract class TypeAdapter
{
    /**
     * Read the next value, convert it to its type and return it
     *
     * @param mixed $value
     * @param ReaderContext $context
     * @return mixed
     */
    abstract public function read($value, ReaderContext $context);

    /**
     * Write the value to the writer for the type
     *
     * @param mixed $value
     * @param WriterContext $context
     * @return mixed
     */
    abstract public function write($value, WriterContext $context);
}
