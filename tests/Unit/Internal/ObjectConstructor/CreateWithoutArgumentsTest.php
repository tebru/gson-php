<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\ObjectConstructor;

use PHPUnit_Framework_TestCase;
use Tebru\Gson\Internal\ObjectConstructor\CreateWithoutArguments;
use Tebru\Gson\Test\Mock\ChildClass;

/**
 * Interface CreateWithoutArgumentsTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\ObjectConstructor\CreateWithoutArguments
 */
class CreateWithoutArgumentsTest extends PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $instanceCreator = new CreateWithoutArguments(ChildClass::class);
        $object = $instanceCreator->construct();

        self::assertInstanceOf(ChildClass::class, $object);
    }
}
