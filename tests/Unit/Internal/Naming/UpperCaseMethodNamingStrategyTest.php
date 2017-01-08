<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\Naming;

use PHPUnit_Framework_TestCase;
use Tebru\Gson\Internal\Naming\UpperCaseMethodNamingStrategy;

/**
 * Class UpperCaseMethodNamingStrategyTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\Naming\UpperCaseMethodNamingStrategy
 */
class UpperCaseMethodNamingStrategyTest extends PHPUnit_Framework_TestCase
{
    public function testSimple()
    {
        $methodNaming = new UpperCaseMethodNamingStrategy();

        self::assertSame(['getFoo', 'isFoo'], $methodNaming->translateToGetter('foo'));
        self::assertSame(['setFoo'], $methodNaming->translateToSetter('foo'));
    }

    public function testAlreadyUpperCase()
    {
        $methodNaming = new UpperCaseMethodNamingStrategy();

        self::assertSame(['getFoo', 'isFoo'], $methodNaming->translateToGetter('Foo'));
        self::assertSame(['setFoo'], $methodNaming->translateToSetter('Foo'));
    }

    public function testUnderscore()
    {
        $methodNaming = new UpperCaseMethodNamingStrategy();

        self::assertSame(['get_foo', 'is_foo'], $methodNaming->translateToGetter('_foo'));
        self::assertSame(['set_foo'], $methodNaming->translateToSetter('_foo'));
    }
}
