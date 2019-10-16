<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\TypeAdapter;

use PHPUnit\Framework\TestCase;
use Tebru\Gson\TypeAdapter\IntegerTypeAdapter;

/**
 * Class IntegerTypeAdapterTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\TypeAdapter\IntegerTypeAdapter
 * @covers \Tebru\Gson\TypeAdapter
 */
class IntegerTypeAdapterTest extends TestCase
{
    public function testNull(): void
    {
        $adapter = new IntegerTypeAdapter();

        self::assertNull($adapter->readFromJson('null'));
    }

    public function testRead(): void
    {
        $adapter = new IntegerTypeAdapter();

        self::assertSame(1, $adapter->readFromJson('1'));
    }

    public function testSerializeNull(): void
    {
        $adapter = new IntegerTypeAdapter();

        self::assertSame('null', $adapter->writeToJson(null, false));
    }

    public function testSerializeFloat(): void
    {
        $adapter = new IntegerTypeAdapter();

        self::assertSame('1', $adapter->writeToJson(1, false));
    }
}
