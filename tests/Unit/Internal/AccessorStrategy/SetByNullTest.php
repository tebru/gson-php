<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
namespace Tebru\Gson\Test\Unit\Internal\AccessorStrategy;

use PHPUnit\Framework\TestCase;
use Tebru\Gson\Internal\AccessorStrategy\SetByNull;
use Tebru\Gson\Test\Mock\Unit\Internal\AccessorStrategy\SetByNullTest\SetByNullTestMock;

/**
 * Class SetByNullTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\AccessorStrategy\SetByNull
 */
class SetByNullTest extends TestCase
{
    public function testSetDoesNothing(): void
    {
        $mock = new SetByNullTestMock();
        $strategy = new SetByNull();
        $strategy->set($mock, 1);

        self::assertEquals(new SetByNullTestMock(), $mock);
    }
}
