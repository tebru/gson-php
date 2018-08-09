<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\ObjectConstructor;

use PHPUnit\Framework\TestCase;
use Tebru\Gson\Internal\ObjectConstructor\CreateFromInstance;
use Tebru\Gson\Test\Mock\UserMock;

/**
 * Class CreateFromInstanceTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\ObjectConstructor\CreateFromInstance
 */
class CreateFromInstanceTest extends TestCase
{
    public function testConstruct(): void
    {
        $mock = new UserMock();
        $instanceCreator = new CreateFromInstance($mock);
        $object = $instanceCreator->construct();

        self::assertSame($mock, $object);
    }
}
