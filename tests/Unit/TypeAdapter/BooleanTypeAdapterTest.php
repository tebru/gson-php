<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\TypeAdapter;

use Tebru\Gson\TypeAdapter\BooleanTypeAdapter;

/**
 * Class BooleanTypeAdapterTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\TypeAdapter\BooleanTypeAdapter
 * @covers \Tebru\Gson\TypeAdapter
 */
class BooleanTypeAdapterTest extends TypeAdapterTestCase
{
    public function testDeserializeNull(): void
    {
        $adapter = new BooleanTypeAdapter();

        self::assertNull($adapter->read(json_decode('null', true), $this->readerContext));
    }

    public function testDeserializeReadTrue(): void
    {
        $adapter = new BooleanTypeAdapter();

        self::assertTrue($adapter->read(json_decode('true', true), $this->readerContext));
    }

    public function testDeserializeReadFalse(): void
    {
        $adapter = new BooleanTypeAdapter();

        self::assertFalse($adapter->read(json_decode('false', true), $this->readerContext));
    }

    public function testSerializeNull(): void
    {
        $adapter = new BooleanTypeAdapter();

        self::assertNull($adapter->write(null, $this->writerContext));
    }

    public function testSerializeTrue(): void
    {
        $adapter = new BooleanTypeAdapter();

        self::assertTrue($adapter->write(true, $this->writerContext));
    }

    public function testSerializeFalse(): void
    {
        $adapter = new BooleanTypeAdapter();

        self::assertFalse($adapter->write(false, $this->writerContext));
    }
}
