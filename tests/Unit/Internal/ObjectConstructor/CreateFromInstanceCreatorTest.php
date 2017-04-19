<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\ObjectConstructor;

use PHPUnit_Framework_TestCase;
use Tebru\Gson\Internal\ObjectConstructor\CreateFromInstanceCreator;
use Tebru\Gson\Test\Mock\ChildClass;
use Tebru\Gson\Test\Mock\ClassWithParameters;
use Tebru\Gson\Test\Mock\ClassWithParametersInstanceCreator;
use Tebru\PhpType\TypeToken;

/**
 * Class CreateFromInstanceCreatorTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\ObjectConstructor\CreateFromInstanceCreator
 */
class CreateFromInstanceCreatorTest extends PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $instanceCreator = new CreateFromInstanceCreator(new ClassWithParametersInstanceCreator(), new TypeToken(ChildClass::class));
        $object = $instanceCreator->construct();

        self::assertInstanceOf(ClassWithParameters::class, $object);
        self::assertSame(ChildClass::class, $object->class);
    }
}
