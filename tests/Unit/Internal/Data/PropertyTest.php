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
use Tebru\Gson\Internal\Data\AnnotationSet;
use Tebru\Gson\Internal\Data\Property;
use Tebru\Gson\Internal\JsonDecodeReader;
use Tebru\Gson\Internal\JsonEncodeWriter;
use Tebru\Gson\PhpType;
use Tebru\Gson\Test\Mock\ChildClass;
use Tebru\Gson\Test\Mock\ChildClassParent;
use Tebru\Gson\Test\Mock\TypeAdapterMock;

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
        $className = 'foo';
        $realName = 'foo';
        $serializedName = 'foo_bar';
        $type = new PhpType('Foo');
        $annotationSet = new AnnotationSet();

        $property = new Property(
            $className,
            $realName,
            $serializedName,
            $type,
            new GetByPublicProperty('foo'),
            new SetByPublicProperty('foo'),
            $annotationSet,
            0,
            new TypeAdapterMock()
        );

        self::assertSame($className, $property->getClassName());
        self::assertSame($realName, $property->getRealName());
        self::assertSame($serializedName, $property->getSerializedName());
        self::assertSame($type, $property->getType());
        self::assertSame(0, $property->getModifiers());
        self::assertSame($annotationSet, $property->getAnnotations());
        self::assertFalse($property->skipSerialize());
        self::assertFalse($property->skipDeserialize());
    }

    public function testSetSkipSerializeAndDeserialize()
    {
        $realName = 'foo';
        $serializedName = 'foo_bar';
        $type = new PhpType('Foo');
        $annotationSet = new AnnotationSet();

        $property = new Property(
            'foo',
            $realName,
            $serializedName,
            $type,
            new GetByPublicProperty('foo'),
            new SetByPublicProperty('foo'),
            $annotationSet,
            0,
            new TypeAdapterMock()
        );

        $property->setSkipSerialize(true);
        $property->setSkipDeserialize(true);

        self::assertTrue($property->skipSerialize());
        self::assertTrue($property->skipDeserialize());
    }

    public function testSetAndGet()
    {
        $mock = new class { public $foo; };
        $realName = 'foo';
        $serializedName = 'foo_bar';
        $type = new PhpType('Foo');

        $property = new Property(
            'foo',
            $realName,
            $serializedName,
            $type,
            new GetByPublicProperty('foo'),
            new SetByPublicProperty('foo'),
            new AnnotationSet(),
            0,
            new TypeAdapterMock()
        );

        $property->set($mock, 'bar');
        self::assertSame('bar', $property->get($mock));
    }

    public function testSetAndGetFromParentMethod()
    {
        $mock = new ChildClass();
        $realName = 'foo';
        $serializedName = 'foo_bar';
        $type = new PhpType('Foo');

        $property = new Property(
            'foo',
            $realName,
            $serializedName,
            $type,
            new GetByMethod('getOverridden'),
            new SetByMethod('setOverridden'),
            new AnnotationSet(),
            0,
            new TypeAdapterMock()
        );

        $property->set($mock, 'bar');
        self::assertSame('bar', $property->get($mock));
    }

    public function testSetAndGetFromParentPublic()
    {
        $mock = new ChildClass();
        $realName = 'foo';
        $serializedName = 'foo_bar';
        $type = new PhpType('Foo');

        $property = new Property(
            'foo',
            $realName,
            $serializedName,
            $type,
            new GetByPublicProperty('qux'),
            new SetByPublicProperty('qux'),
            new AnnotationSet(),
            0,
            new TypeAdapterMock()
        );

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

        $property = new Property(
            'foo',
            $realName,
            $serializedName,
            $type,
            $getter,
            $setter,
            new AnnotationSet(),
            0,
            new TypeAdapterMock()
        );

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

        $property = new Property(
            'foo',
            $realName,
            $serializedName,
            $type,
            $getter,
            $setter,
            new AnnotationSet(),
            0,
            new TypeAdapterMock()
        );

        $property->set($mock, 'bar');
        self::assertSame('bar', $property->get($mock));
    }

    public function testRead()
    {
        $mock = new class { public $foo; };
        $realName = 'foo';
        $serializedName = 'foo_bar';
        $type = new PhpType('Foo');

        $property = new Property(
            'foo',
            $realName,
            $serializedName,
            $type,
            new GetByPublicProperty('foo'),
            new SetByPublicProperty('foo'),
            new AnnotationSet(),
            0,
            new TypeAdapterMock()
        );

        $property->read(new JsonDecodeReader('"foo"'), $mock);
        self::assertSame('foo', $property->get($mock));
    }

    public function testWrite()
    {
        $mock = new class { public $foo = 'bar'; };
        $realName = 'foo';
        $serializedName = 'foo_bar';
        $type = new PhpType('Foo');

        $property = new Property(
            'foo',
            $realName,
            $serializedName,
            $type,
            new GetByPublicProperty('foo'),
            new SetByPublicProperty('foo'),
            new AnnotationSet(),
            0,
            new TypeAdapterMock()
        );

        $writer = new JsonEncodeWriter();
        $property->write($writer, $mock);
        self::assertSame('"bar"', (string) $writer);
    }

    public function testSetNull()
    {
        $mock = new class { public $foo; };
        $realName = 'foo';
        $serializedName = 'foo_bar';
        $type = new PhpType('Foo');

        $property = new Property(
            'foo',
            $realName,
            $serializedName,
            $type,
            new GetByPublicProperty('foo'),
            new SetByPublicProperty('foo'),
            new AnnotationSet(),
            0,
            new TypeAdapterMock()
        );

        $property->set($mock, null);
        self::assertNull($property->get($mock));
    }
}
