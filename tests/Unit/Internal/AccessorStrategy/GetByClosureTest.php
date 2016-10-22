<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\AccessorStrategy;

use Closure;
use PHPUnit_Framework_TestCase;
use Tebru\Gson\Internal\AccessorStrategy\GetByClosure;
use Tebru\Gson\Test\Mock\ClosureTestMock;
use Throwable;

/**
 * Class GetByClosureTest
 *
 * @author Nate Brunette <n@tebru.net>
 */
class GetByClosureTest extends PHPUnit_Framework_TestCase
{
    public function testGetter()
    {
        $strategy = new GetByClosure('foo', ClosureTestMock::class);

        self::assertSame('bar', $strategy->get(new ClosureTestMock()));
    }

    public function testGetterNoProperty()
    {
        $strategy = new GetByClosure('foobar', ClosureTestMock::class);

        try {
            $strategy->get(new ClosureTestMock());

            self::assertTrue(false);
        } catch (Throwable $throwable) {
            self::assertTrue(true);
        }
    }

    public function testGetterUsesCache()
    {
        $strategy = new GetByClosure('foo', ClosureTestMock::class);

        $strategy->get(new ClosureTestMock());

        $closure = Closure::bind(function () {
            return $this->getter;
        }, $strategy, GetByClosure::class);

        $getter = $closure();

        $strategy->get(new ClosureTestMock());

        $getter2 = $closure();

        self::assertSame($getter, $getter2);
    }
}
