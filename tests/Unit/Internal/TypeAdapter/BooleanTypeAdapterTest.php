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
 */
class BooleanTypeAdapterTest extends PHPUnit_Framework_TestCase
{
    public function testNull()
    {
        $adapter = new BooleanTypeAdapter();

        self::assertNull($adapter->readFromJson('null'));
    }

    public function testReadTrue()
    {
        $adapter = new BooleanTypeAdapter();

        self::assertTrue($adapter->readFromJson('true'));
    }

    public function testReadFalse()
    {
        $adapter = new BooleanTypeAdapter();

        self::assertFalse($adapter->readFromJson('false'));
    }
}
