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
 * @covers \Tebru\Gson\TypeAdapter
 */
class FloatTypeAdapterTest extends PHPUnit_Framework_TestCase
{
    public function testDeserializeNull()
    {
        $adapter = new FloatTypeAdapter();

        self::assertNull($adapter->readFromJson('null'));
    }

    public function testDeserializeRead()
    {
        $adapter = new FloatTypeAdapter();
        $result = $adapter->readFromJson('1.1');

        self::assertSame(1.1, $result);
    }

    public function testDeserializeReadIntegerToFloat()
    {
        $adapter = new FloatTypeAdapter();
        $result = $adapter->readFromJson('1');

        self::assertSame(1.0, $result);
    }

    public function testSerializeNull()
    {
        $adapter = new FloatTypeAdapter();

        self::assertSame('null', $adapter->writeToJson(null, false));
    }

    public function testSerializeFloat()
    {
        $adapter = new FloatTypeAdapter();

        self::assertSame('1.1', $adapter->writeToJson(1.1, false));
    }

    public function testSerializeFloatAsInt()
    {
        $adapter = new FloatTypeAdapter();

        self::assertSame('1', $adapter->writeToJson(1, false));
    }
}
