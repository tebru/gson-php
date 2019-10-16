<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\TypeAdapter;

use PHPUnit\Framework\TestCase;
use Tebru\Gson\TypeAdapter\NullTypeAdapter;

/**
 * Class NullTypeAdapterTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\TypeAdapter\NullTypeAdapter
 * @covers \Tebru\Gson\TypeAdapter
 */
class NullTypeAdapterTest extends TestCase
{
    public function testRead(): void
    {
        $adapter = new NullTypeAdapter();

        self::assertNull($adapter->readFromJson('null'));
    }

    public function testWrite(): void
    {
        $adapter = new NullTypeAdapter();

        self::assertSame('null', $adapter->writeToJson(null, false));
    }
}
