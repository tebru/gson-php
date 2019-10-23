<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\TypeAdapter;

use Tebru\Gson\TypeAdapter\NullTypeAdapter;

/**
 * Class NullTypeAdapterTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\TypeAdapter\NullTypeAdapter
 * @covers \Tebru\Gson\TypeAdapter
 */
class NullTypeAdapterTest extends TypeAdapterTestCase
{
    public function testRead(): void
    {
        $adapter = new NullTypeAdapter();

        self::assertNull($adapter->read(json_decode('null', true), $this->readerContext));
    }

    public function testWrite(): void
    {
        $adapter = new NullTypeAdapter();

        self::assertNull($adapter->write(null, $this->writerContext));
    }
}
