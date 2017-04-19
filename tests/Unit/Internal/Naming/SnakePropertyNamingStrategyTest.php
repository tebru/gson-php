<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\Naming;

use PHPUnit_Framework_TestCase;
use Tebru\Gson\Internal\Naming\SnakePropertyNamingStrategy;

/**
 * Class SnakePropertyNamingStrategyTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\Naming\SnakePropertyNamingStrategy
 */
class SnakePropertyNamingStrategyTest extends PHPUnit_Framework_TestCase
{
    public function testNoTransform()
    {
        $propertyNaming = new SnakePropertyNamingStrategy();

        self::assertSame('foo', $propertyNaming->translateName('foo'));
    }

    public function testSimpleTransform()
    {
        $propertyNaming = new SnakePropertyNamingStrategy();

        self::assertSame('foo_bar', $propertyNaming->translateName('fooBar'));
    }

    public function testTwoUpperCase()
    {
        $propertyNaming = new SnakePropertyNamingStrategy();

        self::assertSame('foo_bar_baz', $propertyNaming->translateName('fooBarBaz'));
    }

    public function testTwoUpperCaseInARow()
    {
        $propertyNaming = new SnakePropertyNamingStrategy();

        self::assertSame('foo_b_bar_baz', $propertyNaming->translateName('fooBBarBaz'));
    }

    public function testNumbers()
    {
        $propertyNaming = new SnakePropertyNamingStrategy();

        self::assertSame('foo1_bar', $propertyNaming->translateName('foo1Bar'));
    }

    public function testUnderscore()
    {
        $propertyNaming = new SnakePropertyNamingStrategy();

        self::assertSame('foo__bar', $propertyNaming->translateName('foo_Bar'));
    }
}
