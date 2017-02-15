<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter;

use PHPUnit_Framework_TestCase;
use Tebru\Gson\Internal\TypeAdapter\StringTypeAdapter;

/**
 * Class StringTypeAdapterTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\TypeAdapter\StringTypeAdapter
 * @covers \Tebru\Gson\TypeAdapter
 */
class StringTypeAdapterTest extends PHPUnit_Framework_TestCase
{
    public function testNull()
    {
        $adapter = new StringTypeAdapter();

        self::assertNull($adapter->readFromJson('null'));
    }

    public function testRead()
    {
        $adapter = new StringTypeAdapter();

        self::assertSame('foo', $adapter->readFromJson('"foo"'));
    }

    public function testReadNumberInString()
    {
        $adapter = new StringTypeAdapter();

        self::assertSame('1', $adapter->readFromJson('"1"'));
    }

    public function testSerializeNull()
    {
        $adapter = new StringTypeAdapter();

        self::assertSame('null', $adapter->writeToJson(null, false));
    }

    public function testSerializeFloat()
    {
        $adapter = new StringTypeAdapter();

        self::assertSame('"foo"', $adapter->writeToJson('foo', false));
    }
}
