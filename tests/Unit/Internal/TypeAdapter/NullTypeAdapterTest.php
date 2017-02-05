<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter;

use PHPUnit_Framework_TestCase;
use Tebru\Gson\Internal\TypeAdapter\NullTypeAdapter;

/**
 * Class NullTypeAdapterTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\TypeAdapter\NullTypeAdapter
 */
class NullTypeAdapterTest extends PHPUnit_Framework_TestCase
{
    public function testRead()
    {
        $adapter = new NullTypeAdapter();

        self::assertNull($adapter->readFromJson('null'));
    }
}
