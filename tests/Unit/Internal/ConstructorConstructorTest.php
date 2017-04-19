<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal;

use PHPUnit_Framework_TestCase;
use Tebru\Gson\InstanceCreator;
use Tebru\Gson\Internal\ConstructorConstructor;
use Tebru\Gson\Internal\ObjectConstructor\CreateFromInstanceCreator;
use Tebru\Gson\Internal\ObjectConstructor\CreateFromReflectionClass;
use Tebru\Gson\Internal\ObjectConstructor\CreateWithoutArguments;
use Tebru\Gson\Test\Mock\ChildClass;
use Tebru\Gson\Test\Mock\ClassWithParameters;
use Tebru\Gson\Test\Mock\ClassWithParametersInstanceCreator;
use Tebru\PhpType\TypeToken;

/**
 * Interface ConstructorConstructorTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\ConstructorConstructor
 */
class ConstructorConstructorTest extends PHPUnit_Framework_TestCase
{
    public function testCreateFromInstanceCreator()
    {
        $instanceCreators = [ClassWithParameters::class => new ClassWithParametersInstanceCreator()];
        $constructorConstructor = new ConstructorConstructor($instanceCreators);
        $object = $constructorConstructor->get(new TypeToken(ClassWithParameters::class));

        self::assertInstanceOf(CreateFromInstanceCreator::class, $object);
    }

    public function testCreateFromInstanceCreatorInterface()
    {
        $instanceCreators = [InstanceCreator::class => new ClassWithParametersInstanceCreator()];
        $constructorConstructor = new ConstructorConstructor($instanceCreators);
        $object = $constructorConstructor->get(new TypeToken(ClassWithParametersInstanceCreator::class));

        self::assertInstanceOf(CreateFromInstanceCreator::class, $object);
    }

    public function testCreateWithoutArguments()
    {
        $constructorConstructor = new ConstructorConstructor();
        $object = $constructorConstructor->get(new TypeToken(ChildClass::class));

        self::assertInstanceOf(CreateWithoutArguments::class, $object);
    }

    public function testCreateFromReflectionClass()
    {
        $constructorConstructor = new ConstructorConstructor();
        $object = $constructorConstructor->get(new TypeToken(ClassWithParameters::class));

        self::assertInstanceOf(CreateFromReflectionClass::class, $object);
    }
}
