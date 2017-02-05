<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter;

use PHPUnit_Framework_TestCase;
use Tebru\Gson\Internal\TypeAdapter\FloatTypeAdapter;

/**
 * Class FloatTypeAdapterTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\TypeAdapter\FloatTypeAdapter
 */
class FloatTypeAdapterTest extends PHPUnit_Framework_TestCase
{
    public function testNull()
    {
        $adapter = new FloatTypeAdapter();

        self::assertNull($adapter->readFromJson('null'));
    }

    public function testRead()
    {
        $adapter = new FloatTypeAdapter();
        $result = $adapter->readFromJson('1.1');

        self::assertSame(1.1, $result);
    }

    public function testReadIntegerToFloat()
    {
        $adapter = new FloatTypeAdapter();
        $result = $adapter->readFromJson('1');

        self::assertSame(1.0, $result);
    }
}
