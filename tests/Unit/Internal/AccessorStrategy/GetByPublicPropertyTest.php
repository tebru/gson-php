<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\AccessorStrategy;

use PHPUnit\Framework\TestCase;
use Tebru\Gson\Internal\AccessorStrategy\GetByPublicProperty;
use Tebru\Gson\Test\Mock\Unit\Internal\AccessorStrategy\GetByPublicPropertyTest\GetByPublicPropertyTestMock;
use Throwable;

/**
 * Class GetByPublicPropertyTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\AccessorStrategy\GetByPublicProperty
 */
class GetByPublicPropertyTest extends TestCase
{
    public function testGetter(): void
    {
        $strategy = new GetByPublicProperty('foo');

        self::assertSame('bar', $strategy->get(new GetByPublicPropertyTestMock()));
    }

    public function testGetterNoProperty(): void
    {
        $strategy = new GetByPublicProperty('foo2');

        try {
            $strategy->get(new GetByPublicPropertyTestMock());
        } catch (Throwable $throwable) {
            self::assertTrue(true);
            return;
        }
        self::assertTrue(false);
    }
}
