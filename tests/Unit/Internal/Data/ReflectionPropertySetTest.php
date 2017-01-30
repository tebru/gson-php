<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\Data;

use PHPUnit_Framework_TestCase;
use ReflectionProperty;
use Tebru\Gson\Internal\Data\ReflectionPropertySet;
use Tebru\Gson\Test\Mock\ChildClass;
use Tebru\Gson\Test\Mock\ChildClassParent;

/**
 * Class ReflectionPropertySetTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\Data\ReflectionPropertySet
 */
class ReflectionPropertySetTest extends PHPUnit_Framework_TestCase
{
    public function testAddProperty()
    {
        $property = new ReflectionProperty(ChildClass::class, 'foo');
        $set = new ReflectionPropertySet();
        $set->add($property);

        self::assertSame([$property], $set->toArray());
    }

    public function testAddSamePropertyName()
    {
        $property = new ReflectionProperty(ChildClass::class, 'foo');
        $set = new ReflectionPropertySet();
        $set->add($property);
        $set->add(new ReflectionProperty(ChildClassParent::class, 'foo'));

        self::assertSame([$property], $set->toArray());
    }

    public function testClear()
    {
        $set = new ReflectionPropertySet([new ReflectionProperty(ChildClass::class, 'foo')]);
        $set->clear();

        self::assertCount(0, $set);
    }

    public function testContainsTrue()
    {
        $property = new ReflectionProperty(ChildClass::class, 'foo');
        $set = new ReflectionPropertySet([$property]);

        self::assertTrue($set->contains(new ReflectionProperty(ChildClass::class, 'foo')));
    }

    public function testContainsFalse()
    {
        $property = new ReflectionProperty(ChildClass::class, 'foo');
        $set = new ReflectionPropertySet([$property]);

        self::assertFalse($set->contains(new ReflectionProperty(ChildClass::class, 'overridden')));
    }

    public function testRemove()
    {
        $property = new ReflectionProperty(ChildClass::class, 'foo');
        $set = new ReflectionPropertySet([$property]);
        $removed = $set->remove($property);

        self::assertTrue($removed);
        self::assertCount(0, $set);
    }

    public function testRemoveFalse()
    {
        $property = new ReflectionProperty(ChildClass::class, 'foo');
        $set = new ReflectionPropertySet([$property]);
        $removed = $set->remove(new ReflectionProperty(ChildClass::class, 'overridden'));

        self::assertFalse($removed);
        self::assertCount(1, $set);
    }

    public function testToArray()
    {
        $property = new ReflectionProperty(ChildClass::class, 'foo');
        $set = new ReflectionPropertySet([$property]);

        self::assertSame([$property], $set->toArray());
    }

    public function testCanIterate()
    {
        $annotation = new ReflectionProperty(ChildClass::class, 'foo');
        $set = new ReflectionPropertySet([$annotation]);

        foreach ($set as $element) {
            self::assertSame($annotation, $element);
        }
    }
}
