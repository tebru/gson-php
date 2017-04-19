<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\AccessorStrategy;

use PHPUnit_Framework_TestCase;
use Tebru\Gson\Internal\AccessorStrategy\SetByMethod;
use Tebru\Gson\Test\Mock\Unit\Internal\AccessorStrategy\SetByMethodTest\SetByMethodTestMock;
use Throwable;

/**
 * Class SetByMethodTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\AccessorStrategy\SetByMethod
 */
class SetByMethodTest extends PHPUnit_Framework_TestCase
{
    public function testSetter()
    {
        $mock = new SetByMethodTestMock();

        $strategy = new SetByMethod('setFoo');
        $strategy->set($mock, 'bar');

        self::assertSame('bar', $mock->foo);
    }

    public function testSetterNoMethod()
    {
        $strategy = new SetByMethod('foo');
        try {
            $strategy->set(new SetByMethodTestMock(), 'bar');
        } catch (Throwable $throwable) {
            self::assertSame('Call to undefined method Tebru\Gson\Test\Mock\Unit\Internal\AccessorStrategy\SetByMethodTest\SetByMethodTestMock::foo()', $throwable->getMessage());
            return;
        }
        self::assertTrue(false);
    }
}
