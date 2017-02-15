<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter;

use PHPUnit_Framework_TestCase;
use Tebru\Gson\Internal\TypeAdapter\BooleanTypeAdapter;

/**
 * Class BooleanTypeAdapterTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\TypeAdapter\BooleanTypeAdapter
 * @covers \Tebru\Gson\TypeAdapter
 */
class BooleanTypeAdapterTest extends PHPUnit_Framework_TestCase
{
    public function testDeserializeNull()
    {
        $adapter = new BooleanTypeAdapter();

        self::assertNull($adapter->readFromJson('null'));
    }

    public function testDeserializeReadTrue()
    {
        $adapter = new BooleanTypeAdapter();

        self::assertTrue($adapter->readFromJson('true'));
    }

    public function testDeserializeReadFalse()
    {
        $adapter = new BooleanTypeAdapter();

        self::assertFalse($adapter->readFromJson('false'));
    }

    public function testSerializeNull()
    {
        $adapter = new BooleanTypeAdapter();

        self::assertSame('null', $adapter->writeToJson(null, false));
    }

    public function testSerializeTrue()
    {
        $adapter = new BooleanTypeAdapter();

        self::assertSame('true', $adapter->writeToJson(true, false));
    }

    public function testSerializeFalse()
    {
        $adapter = new BooleanTypeAdapter();

        self::assertSame('false', $adapter->writeToJson(false, false));
    }
}
