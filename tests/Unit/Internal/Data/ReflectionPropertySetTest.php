<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\Data;

use PHPUnit\Framework\TestCase;
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
class ReflectionPropertySetTest extends TestCase
{
    public function testAddProperty(): void
    {
        $property = new ReflectionProperty(ChildClass::class, 'foo');
        $set = new ReflectionPropertySet();
        $set->add($property);

        self::assertSame([$property], $set->toArray());
    }

    public function testAddSamePropertyName(): void
    {
        $property = new ReflectionProperty(ChildClass::class, 'foo');
        $set = new ReflectionPropertySet();
        $set->add($property);
        $set->add(new ReflectionProperty(ChildClassParent::class, 'foo'));

        self::assertSame([$property], $set->toArray());
    }

    public function testToArray(): void
    {
        $property = new ReflectionProperty(ChildClass::class, 'foo');
        $set = new ReflectionPropertySet();
        $set->add($property);

        self::assertSame([$property], $set->toArray());
    }

    public function testCanIterate(): void
    {
        $annotation = new ReflectionProperty(ChildClass::class, 'foo');
        $set = new ReflectionPropertySet();
        $set->add($annotation);

        foreach ($set as $element) {
            self::assertSame($annotation, $element);
        }
    }
}
