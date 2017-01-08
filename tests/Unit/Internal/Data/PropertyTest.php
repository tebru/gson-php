<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\Data;

use PHPUnit_Framework_TestCase;
use Tebru\Gson\Internal\AccessorStrategy\GetByClosure;
use Tebru\Gson\Internal\AccessorStrategy\GetByMethod;
use Tebru\Gson\Internal\AccessorStrategy\GetByPublicProperty;
use Tebru\Gson\Internal\AccessorStrategy\SetByClosure;
use Tebru\Gson\Internal\AccessorStrategy\SetByMethod;
use Tebru\Gson\Internal\AccessorStrategy\SetByPublicProperty;
use Tebru\Gson\Internal\Data\Property;
use Tebru\Gson\Internal\PhpType;
use Tebru\Gson\Test\Mock\ChildClass;
use Tebru\Gson\Test\Mock\ChildClassParent;

/**
 * Class PropertyTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\Data\Property
 */
class PropertyTest extends PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $realName = 'foo';
        $serializedName = 'foo_bar';
        $type = new PhpType('Foo');

        $property = new Property($realName, $serializedName, $type, new GetByPublicProperty('foo'), new SetByPublicProperty('foo'));

        self::assertSame($realName, $property->getRealName());
        self::assertSame($serializedName, $property->getSerializedName());
        self::assertSame($type, $property->getType());
    }

    public function testSetAndGet()
    {
        $mock = new class { public $foo; };
        $realName = 'foo';
        $serializedName = 'foo_bar';
        $type = new PhpType('Foo');

        $property = new Property($realName, $serializedName, $type, new GetByPublicProperty('foo'), new SetByPublicProperty('foo'));

        $property->set($mock, 'bar');
        self::assertSame('bar', $property->get($mock));
    }

    public function testSetAndGetFromParentMethod()
    {
        $mock = new ChildClass();
        $realName = 'foo';
        $serializedName = 'foo_bar';
        $type = new PhpType('Foo');

        $property = new Property($realName, $serializedName, $type, new GetByMethod('getOverridden'), new SetByMethod('setOverridden'));

        $property->set($mock, 'bar');
        self::assertSame('bar', $property->get($mock));
    }

    public function testSetAndGetFromParentPublic()
    {
        $mock = new ChildClass();
        $realName = 'foo';
        $serializedName = 'foo_bar';
        $type = new PhpType('Foo');

        $property = new Property($realName, $serializedName, $type, new GetByPublicProperty('qux'), new SetByPublicProperty('qux'));

        $property->set($mock, 'bar');
        self::assertSame('bar', $property->get($mock));
    }

    public function testSetAndGetFromParentClosure()
    {
        $mock = new ChildClass();
        $realName = 'foo';
        $serializedName = 'foo_bar';
        $type = new PhpType('Foo');
        $getter = new GetByClosure('bar', ChildClassParent::class);
        $setter = new SetByClosure('bar', ChildClassParent::class);

        $property = new Property($realName, $serializedName, $type, $getter, $setter);

        $property->set($mock, 'bar');
        self::assertSame('bar', $property->get($mock));
    }

    public function testSetAndGetFromParentClosureScopedFromChild()
    {
        $mock = new ChildClass();
        $realName = 'foo';
        $serializedName = 'foo_bar';
        $type = new PhpType('Foo');
        $getter = new GetByClosure('bar', ChildClass::class);
        $setter = new SetByClosure('bar', ChildClass::class);

        $property = new Property($realName, $serializedName, $type, $getter, $setter);

        $property->set($mock, 'bar');
        self::assertSame('bar', $property->get($mock));
    }
}
