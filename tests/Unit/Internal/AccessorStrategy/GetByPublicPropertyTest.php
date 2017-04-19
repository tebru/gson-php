<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\AccessorStrategy;

use PHPUnit_Framework_TestCase;
use Tebru\Gson\Internal\AccessorStrategy\GetByPublicProperty;
use Tebru\Gson\Test\Mock\Unit\Internal\AccessorStrategy\GetByPublicPropertyTest\GetByPublicPropertyTestMock;
use Throwable;

/**
 * Class GetByPublicPropertyTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\AccessorStrategy\GetByPublicProperty
 */
class GetByPublicPropertyTest extends PHPUnit_Framework_TestCase
{
    public function testGetter()
    {
        $strategy = new GetByPublicProperty('foo');

        self::assertSame('bar', $strategy->get(new GetByPublicPropertyTestMock()));
    }

    public function testGetterNoProperty()
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
