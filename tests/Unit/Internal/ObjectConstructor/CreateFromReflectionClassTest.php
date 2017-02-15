<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\ObjectConstructor;

use PHPUnit_Framework_TestCase;
use Tebru\Gson\Internal\ObjectConstructor\CreateFromReflectionClass;
use Tebru\Gson\Test\Mock\ClassWithParameters;

/**
 * Interface CreateFromReflectionClassTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\ObjectConstructor\CreateFromReflectionClass
 */
class CreateFromReflectionClassTest extends PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $instanceCreator = new CreateFromReflectionClass(ClassWithParameters::class);
        $object = $instanceCreator->construct();

        self::assertInstanceOf(ClassWithParameters::class, $object);
        self::assertNull($object->class);
    }
}
