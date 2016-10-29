<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\AccessorStrategy;

use PHPUnit_Framework_TestCase;
use Tebru\Gson\Internal\AccessorStrategy\SetByPublicProperty;

/**
 * Class SetByPublicPropertyTest
 *
 * @author Nate Brunette <n@tebru.net>
 */
class SetByPublicPropertyTest extends PHPUnit_Framework_TestCase
{
    public function testSetter()
    {
        $mock = new class {
            public $foo;
        };

        $strategy = new SetByPublicProperty('foo');
        $strategy->set($mock, 'bar');

        self::assertSame('bar', $mock->foo);
    }

    public function testSetterNoProperty()
    {
        $mock = new class {};

        $strategy = new SetByPublicProperty('foo');
        $strategy->set($mock, 'bar');

        self::assertSame('bar', $mock->foo);
    }
}
