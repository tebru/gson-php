<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\AccessorStrategy;

use PHPUnit_Framework_TestCase;
use Tebru\Gson\Internal\AccessorStrategy\GetByMethod;
use Tebru\Gson\Test\Mock\Unit\Internal\AccessorStrategy\GetByMethodTest\GetByMethodTestMock;
use Throwable;

/**
 * Class GetByMethodTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\AccessorStrategy\GetByMethod
 */
class GetByMethodTest extends PHPUnit_Framework_TestCase
{
    public function testGetter()
    {
        $strategy = new GetByMethod('foo');

        self::assertSame('bar', $strategy->get(new GetByMethodTestMock()));
    }

    public function testGetterNoMethod()
    {
        $strategy = new GetByMethod('bar');
        try {
            $strategy->get(new GetByMethodTestMock());
        } catch (Throwable $throwable) {
            self::assertSame('Call to undefined method Tebru\Gson\Test\Mock\Unit\Internal\AccessorStrategy\GetByMethodTest\GetByMethodTestMock::bar()', $throwable->getMessage());
            return;
        }
        self::assertTrue(false);
    }
}
