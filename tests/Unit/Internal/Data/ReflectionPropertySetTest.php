<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\Data;

use PHPUnit_Framework_TestCase;
use ReflectionProperty;
use Tebru\Gson\Internal\Data\ReflectionPropertySet;
use Tebru\Gson\Test\Mock\ClosureTestMock;

/**
 * Class ReflectionPropertySetTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\Data\ReflectionPropertySet
 */
class ReflectionPropertySetTest extends PHPUnit_Framework_TestCase
{
    public function testGetKeyUsesClass()
    {
        $set = new ReflectionPropertySet();

        self::assertSame('foo', $set->getKey(new ReflectionProperty(ClosureTestMock::class, 'foo')));
    }
}
