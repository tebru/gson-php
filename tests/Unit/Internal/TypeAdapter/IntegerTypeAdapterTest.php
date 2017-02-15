<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter;

use PHPUnit_Framework_TestCase;
use Tebru\Gson\Internal\TypeAdapter\IntegerTypeAdapter;

/**
 * Class IntegerTypeAdapterTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\TypeAdapter\IntegerTypeAdapter
 * @covers \Tebru\Gson\TypeAdapter
 */
class IntegerTypeAdapterTest extends PHPUnit_Framework_TestCase
{
    public function testNull()
    {
        $adapter = new IntegerTypeAdapter();

        self::assertNull($adapter->readFromJson('null'));
    }

    public function testRead()
    {
        $adapter = new IntegerTypeAdapter();

        self::assertSame(1, $adapter->readFromJson('1'));
    }

    public function testSerializeNull()
    {
        $adapter = new IntegerTypeAdapter();

        self::assertSame('null', $adapter->writeToJson(null, false));
    }

    public function testSerializeFloat()
    {
        $adapter = new IntegerTypeAdapter();

        self::assertSame('1', $adapter->writeToJson(1, false));
    }
}
