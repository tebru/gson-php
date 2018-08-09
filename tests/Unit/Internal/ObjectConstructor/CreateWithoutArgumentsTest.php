<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\ObjectConstructor;

use PHPUnit\Framework\TestCase;
use Tebru\Gson\Internal\ObjectConstructor\CreateWithoutArguments;
use Tebru\Gson\Test\Mock\ChildClass;

/**
 * Interface CreateWithoutArgumentsTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\ObjectConstructor\CreateWithoutArguments
 */
class CreateWithoutArgumentsTest extends TestCase
{
    public function testConstruct(): void
    {
        $instanceCreator = new CreateWithoutArguments(ChildClass::class);
        $object = $instanceCreator->construct();

        self::assertInstanceOf(ChildClass::class, $object);
    }
}
