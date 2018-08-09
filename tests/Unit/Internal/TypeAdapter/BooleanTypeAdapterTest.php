<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter;

use PHPUnit\Framework\TestCase;
use Tebru\Gson\Internal\TypeAdapter\BooleanTypeAdapter;

/**
 * Class BooleanTypeAdapterTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\TypeAdapter\BooleanTypeAdapter
 * @covers \Tebru\Gson\TypeAdapter
 */
class BooleanTypeAdapterTest extends TestCase
{
    public function testDeserializeNull(): void
    {
        $adapter = new BooleanTypeAdapter();

        self::assertNull($adapter->readFromJson('null'));
    }

    public function testDeserializeReadTrue(): void
    {
        $adapter = new BooleanTypeAdapter();

        self::assertTrue($adapter->readFromJson('true'));
    }

    public function testDeserializeReadFalse(): void
    {
        $adapter = new BooleanTypeAdapter();

        self::assertFalse($adapter->readFromJson('false'));
    }

    public function testSerializeNull(): void
    {
        $adapter = new BooleanTypeAdapter();

        self::assertSame('null', $adapter->writeToJson(null, false));
    }

    public function testSerializeTrue(): void
    {
        $adapter = new BooleanTypeAdapter();

        self::assertSame('true', $adapter->writeToJson(true, false));
    }

    public function testSerializeFalse(): void
    {
        $adapter = new BooleanTypeAdapter();

        self::assertSame('false', $adapter->writeToJson(false, false));
    }
}
