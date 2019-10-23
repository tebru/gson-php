<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\TypeAdapter;

use Tebru\Gson\TypeAdapter\StringTypeAdapter;

/**
 * Class StringTypeAdapterTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\TypeAdapter\StringTypeAdapter
 * @covers \Tebru\Gson\TypeAdapter
 */
class StringTypeAdapterTest extends TypeAdapterTestCase
{
    public function testNull(): void
    {
        $adapter = new StringTypeAdapter();

        self::assertNull($adapter->read(json_decode('null', true), $this->readerContext));
    }

    public function testRead(): void
    {
        $adapter = new StringTypeAdapter();

        self::assertSame('foo', $adapter->read(json_decode('"foo"', true), $this->readerContext));
    }

    public function testReadNumberInString(): void
    {
        $adapter = new StringTypeAdapter();

        self::assertSame('1', $adapter->read(json_decode('"1"', true), $this->readerContext));
    }

    public function testSerializeNull(): void
    {
        $adapter = new StringTypeAdapter();

        self::assertNull($adapter->write(null, $this->writerContext));
    }

    public function testSerializeFloat(): void
    {
        $adapter = new StringTypeAdapter();

        self::assertSame('foo', $adapter->write('foo', $this->writerContext));
    }
}
