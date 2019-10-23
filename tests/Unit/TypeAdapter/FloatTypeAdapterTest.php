<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\TypeAdapter;

use Tebru\Gson\TypeAdapter\FloatTypeAdapter;

/**
 * Class FloatTypeAdapterTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\TypeAdapter\FloatTypeAdapter
 * @covers \Tebru\Gson\TypeAdapter
 */
class FloatTypeAdapterTest extends TypeAdapterTestCase
{
    public function testDeserializeNull(): void
    {
        $adapter = new FloatTypeAdapter();

        self::assertNull($adapter->read(json_decode('null', true), $this->readerContext));
    }

    public function testDeserializeRead(): void
    {
        $adapter = new FloatTypeAdapter();
        $result = $adapter->read(json_decode('1.1', true), $this->readerContext);

        self::assertSame(1.1, $result);
    }

    public function testDeserializeReadIntegerToFloat(): void
    {
        $adapter = new FloatTypeAdapter();
        $result = $adapter->read(json_decode('1', true), $this->readerContext);

        self::assertSame(1.0, $result);
    }

    public function testSerializeNull(): void
    {
        $adapter = new FloatTypeAdapter();

        self::assertNull($adapter->write(null, $this->writerContext));
    }

    public function testSerializeFloat(): void
    {
        $adapter = new FloatTypeAdapter();

        self::assertSame(1.1, $adapter->write(1.1, $this->writerContext));
    }

    public function testSerializeFloatAsInt(): void
    {
        $adapter = new FloatTypeAdapter();

        self::assertSame(1.0, $adapter->write(1, $this->writerContext));
    }
}
