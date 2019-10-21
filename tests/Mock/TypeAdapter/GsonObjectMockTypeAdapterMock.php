<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock\TypeAdapter;

use Tebru\Gson\Context\ReaderContext;
use Tebru\Gson\Context\WriterContext;
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
     * @param mixed $value
     * @param ReaderContext $context
     * @return mixed
     */
    public function read($value, ReaderContext $context)
    {
        return new GsonObjectMock($value['foo']);
    }

    /**
     * Write the value to the writer for the type
     *
     * @param mixed $value
     * @param WriterContext $context
     * @return mixed
     */
    public function write($value, WriterContext $context)
    {
        return null;
    }
}
