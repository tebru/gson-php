<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\AccessorStrategy;

use PHPUnit_Framework_TestCase;
use Tebru\Gson\Internal\AccessorStrategy\SetByMethod;
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
        $mock = new class {
            public $foo;
            public function setFoo($foo) {
                $this->foo = $foo;
            }
        };

        $strategy = new SetByMethod('setFoo');
        $strategy->set($mock, 'bar');

        self::assertSame('bar', $mock->foo);
    }

    public function testSetterNoMethod()
    {
        $this->expectException(Throwable::class);

        $strategy = new SetByMethod('foo');
        $strategy->set(new class { public function setBar() { } }, 'bar');
    }
}
