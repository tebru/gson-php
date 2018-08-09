<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter;

use PHPUnit\Framework\TestCase;
use Tebru\Gson\Internal\TypeAdapter\StringTypeAdapter;

/**
 * Class StringTypeAdapterTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\TypeAdapter\StringTypeAdapter
 * @covers \Tebru\Gson\TypeAdapter
 */
class StringTypeAdapterTest extends TestCase
{
    public function testNull(): void
    {
        $adapter = new StringTypeAdapter();

        self::assertNull($adapter->readFromJson('null'));
    }

    public function testRead(): void
    {
        $adapter = new StringTypeAdapter();

        self::assertSame('foo', $adapter->readFromJson('"foo"'));
    }

    public function testReadNumberInString(): void
    {
        $adapter = new StringTypeAdapter();

        self::assertSame('1', $adapter->readFromJson('"1"'));
    }

    public function testSerializeNull(): void
    {
        $adapter = new StringTypeAdapter();

        self::assertSame('null', $adapter->writeToJson(null, false));
    }

    public function testSerializeFloat(): void
    {
        $adapter = new StringTypeAdapter();

        self::assertSame('"foo"', $adapter->writeToJson('foo', false));
    }
}
