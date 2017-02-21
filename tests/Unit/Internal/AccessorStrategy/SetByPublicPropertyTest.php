<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\AccessorStrategy;

use PHPUnit_Framework_TestCase;
use Tebru\Gson\Internal\AccessorStrategy\SetByPublicProperty;
use Tebru\Gson\Test\Mock\Unit\Internal\AccessorStrategy\SetByPublicPropertyTest\SetByPublicPropertyTestMock;

/**
 * Class SetByPublicPropertyTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\AccessorStrategy\SetByPublicProperty
 */
class SetByPublicPropertyTest extends PHPUnit_Framework_TestCase
{
    public function testSetter()
    {
        $mock = new SetByPublicPropertyTestMock();

        $strategy = new SetByPublicProperty('foo');
        $strategy->set($mock, 'bar');

        self::assertSame('bar', $mock->foo);
    }

    public function testSetterNoProperty()
    {
        $mock = new SetByPublicPropertyTestMock();

        $strategy = new SetByPublicProperty('foo2');
        $strategy->set($mock, 'bar');

        self::assertNull($mock->foo);
    }
}
