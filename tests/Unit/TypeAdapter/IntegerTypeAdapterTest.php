<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\TypeAdapter;

use Tebru\Gson\TypeAdapter\IntegerTypeAdapter;

/**
 * Class IntegerTypeAdapterTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\TypeAdapter\IntegerTypeAdapter
 * @covers \Tebru\Gson\TypeAdapter
 */
class IntegerTypeAdapterTest extends TypeAdapterTestCase
{
    public function testNull(): void
    {
        $adapter = new IntegerTypeAdapter();

        self::assertNull($adapter->read(json_decode('null', true), $this->readerContext));
    }

    public function testRead(): void
    {
        $adapter = new IntegerTypeAdapter();

        self::assertSame(1, $adapter->read(json_decode('1', true), $this->readerContext));
    }

    public function testSerializeNull(): void
    {
        $adapter = new IntegerTypeAdapter();

        self::assertNull($adapter->write(null, $this->writerContext));
    }

    public function testSerializeFloat(): void
    {
        $adapter = new IntegerTypeAdapter();

        self::assertSame(1, $adapter->write(1, $this->writerContext));
    }
}
