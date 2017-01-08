<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\Data;

use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionProperty;
use Tebru\Gson\Internal\Data\ReflectionPropertySetFactory;
use Tebru\Gson\Test\Mock\ChildClass;
use Tebru\Gson\Test\Mock\ChildClassParent;
use Tebru\Gson\Test\Mock\ChildClassParent2;
use Tebru\Gson\Test\Mock\ClassWithoutParent;

/**
 * Class ReflectionPropertySetFactoryTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\Data\ReflectionPropertySetFactory
 */
class ReflectionPropertySetFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testCreateWithoutParent()
    {
        $factory = new ReflectionPropertySetFactory();
        $properties = $factory->create(new ReflectionClass(ClassWithoutParent::class));

        $expected = [
            new ReflectionProperty(ClassWithoutParent::class, 'foo'),
            new ReflectionProperty(ClassWithoutParent::class, 'bar'),
            new ReflectionProperty(ClassWithoutParent::class, 'baz'),
            new ReflectionProperty(ClassWithoutParent::class, 'qux'),
        ];

        self::assertEquals($expected, $properties->toArray());
    }

    public function testCreateWithParents()
    {
        $factory = new ReflectionPropertySetFactory();
        $properties = $factory->create(new ReflectionClass(ChildClass::class));

        $expected = [
            new ReflectionProperty(ChildClass::class, 'foo'),
            new ReflectionProperty(ChildClass::class, 'overridden'),
            new ReflectionProperty(ChildClass::class, 'withTypehint'),
            new ReflectionProperty(ChildClassParent::class, 'baz'),
            new ReflectionProperty(ChildClassParent2::class, 'qux'),
            new ReflectionProperty(ChildClassParent::class, 'bar'),
        ];

        self::assertEquals($expected, $properties->toArray());
    }
}
