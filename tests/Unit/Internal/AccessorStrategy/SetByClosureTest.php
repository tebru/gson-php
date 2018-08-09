<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\AccessorStrategy;

use PHPUnit\Framework\TestCase;
use Tebru\Gson\Internal\AccessorStrategy\SetByClosure;
use Tebru\Gson\Test\Mock\Unit\Internal\AccessorStrategy\SetByClosureTest\SetByClosureTestMock;

/**
 * Class SetByClosureTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\AccessorStrategy\SetByClosure
 */
class SetByClosureTest extends TestCase
{
    public function testSetter(): void
    {
        $mock = new SetByClosureTestMock();
        $strategy = new SetByClosure('foo', SetByClosureTestMock::class);
        $strategy->set($mock, 'bar2');

        self::assertAttributeSame('bar2', 'foo', $mock);
    }

    public function testSetterNoProperty(): void
    {
        $mock = new SetByClosureTestMock();
        $strategy = new SetByClosure('foo2', SetByClosureTestMock::class);
        $strategy->set($mock, 'bar');

        self::assertAttributeSame('bar', 'foo2', $mock);
    }
}
