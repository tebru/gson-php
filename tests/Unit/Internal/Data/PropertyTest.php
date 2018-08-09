<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\Data;

use PHPUnit\Framework\TestCase;
use stdClass;
use Tebru\AnnotationReader\AnnotationCollection;
use Tebru\Gson\Annotation\VirtualProperty;
use Tebru\Gson\ClassMetadata;
use Tebru\Gson\Internal\AccessorStrategy\GetByClosure;
use Tebru\Gson\Internal\AccessorStrategy\GetByMethod;
use Tebru\Gson\Internal\AccessorStrategy\GetByPublicProperty;
use Tebru\Gson\Internal\AccessorStrategy\SetByClosure;
use Tebru\Gson\Internal\AccessorStrategy\SetByMethod;
use Tebru\Gson\Internal\AccessorStrategy\SetByPublicProperty;
use Tebru\Gson\Internal\Data\Property;
use Tebru\Gson\Internal\Data\PropertyCollection;
use Tebru\Gson\Test\Mock\ChildClass;
use Tebru\Gson\Test\Mock\ChildClassParent;
use Tebru\Gson\Test\MockProvider;
use Tebru\PhpType\TypeToken;

/**
 * Class PropertyTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\Data\Property
 */
class PropertyTest extends TestCase
{
    /**
     * @var PropertyCollection
     */
    private $propertyCollection;

    /**
     * @var ClassMetadata
     */
    private $classMetadata;

    public function setUp()
    {
        $this->propertyCollection = new PropertyCollection();
        $this->classMetadata = MockProvider::classMetadata(stdClass::class, $this->propertyCollection);
    }

    public function testGetters(): void
    {
        $realName = 'foo';
        $serializedName = 'foo_bar';
        $type = new TypeToken(stdClass::class);
        $annotations = new AnnotationCollection();
        $annotations->add(new VirtualProperty([]));

        $property = new Property(
            $realName,
            $serializedName,
            $type,
            new GetByPublicProperty('foo'),
            new SetByPublicProperty('foo'),
            $annotations,
            0,
            false,
            $this->classMetadata
        );
        $this->propertyCollection->add($property);

        self::assertSame($realName, $property->getName());
        self::assertSame($serializedName, $property->getSerializedName());
        self::assertSame($type, $property->getType());
        self::assertSame(stdClass::class, $property->getTypeName());
        self::assertSame($this->classMetadata, $property->getDeclaringClassMetadata());
        self::assertSame(stdClass::class, $property->getDeclaringClassName());
        self::assertSame(0, $property->getModifiers());
        self::assertSame($annotations, $property->getAnnotations());
        self::assertEquals(new VirtualProperty([]), $property->getAnnotation(VirtualProperty::class));
        self::assertFalse($property->skipSerialize());
        self::assertFalse($property->skipDeserialize());
        self::assertFalse($property->isVirtual());
    }

    public function testSetSkipSerializeAndDeserialize(): void
    {
        $realName = 'foo';
        $serializedName = 'foo_bar';
        $type = new TypeToken(stdClass::class);
        $annotations = new AnnotationCollection();

        $property = new Property(
            $realName,
            $serializedName,
            $type,
            new GetByPublicProperty('foo'),
            new SetByPublicProperty('foo'),
            $annotations,
            0,
            false,
            $this->classMetadata
        );
        $this->propertyCollection->add($property);

        $property->setSkipSerialize(true);
        $property->setSkipDeserialize(true);

        self::assertTrue($property->skipSerialize());
        self::assertTrue($property->skipDeserialize());
    }

    public function testSetAndGet(): void
    {
        $mock = new class { public $foo; };
        $realName = 'foo';
        $serializedName = 'foo_bar';
        $type = new TypeToken(stdClass::class);

        $property = new Property(
            $realName,
            $serializedName,
            $type,
            new GetByPublicProperty('foo'),
            new SetByPublicProperty('foo'),
            new AnnotationCollection(),
            0,
            false,
            $this->classMetadata
        );
        $this->propertyCollection->add($property);

        $property->set($mock, 'bar');
        self::assertSame('bar', $property->get($mock));
    }

    public function testSetAndGetFromParentMethod(): void
    {
        $mock = new ChildClass();
        $realName = 'foo';
        $serializedName = 'foo_bar';
        $type = new TypeToken(stdClass::class);

        $property = new Property(
            $realName,
            $serializedName,
            $type,
            new GetByMethod('getOverridden'),
            new SetByMethod('setOverridden'),
            new AnnotationCollection(),
            0,
            false,
            $this->classMetadata
        );
        $this->propertyCollection->add($property);

        $property->set($mock, 'bar');
        self::assertSame('bar', $property->get($mock));
    }

    public function testSetAndGetFromParentPublic(): void
    {
        $mock = new ChildClass();
        $realName = 'foo';
        $serializedName = 'foo_bar';
        $type = new TypeToken(stdClass::class);

        $property = new Property(
            $realName,
            $serializedName,
            $type,
            new GetByPublicProperty('qux'),
            new SetByPublicProperty('qux'),
            new AnnotationCollection(),
            0,
            false,
            $this->classMetadata
        );
        $this->propertyCollection->add($property);

        $property->set($mock, 'bar');
        self::assertSame('bar', $property->get($mock));
    }

    public function testSetAndGetFromParentClosure(): void
    {
        $mock = new ChildClass();
        $realName = 'foo';
        $serializedName = 'foo_bar';
        $type = new TypeToken(stdClass::class);
        $getter = new GetByClosure('bar', ChildClassParent::class);
        $setter = new SetByClosure('bar', ChildClassParent::class);

        $property = new Property(
            $realName,
            $serializedName,
            $type,
            $getter,
            $setter,
            new AnnotationCollection(),
            0,
            false,
            $this->classMetadata
        );
        $this->propertyCollection->add($property);

        $property->set($mock, 'bar');
        self::assertSame('bar', $property->get($mock));
    }

    public function testSetAndGetFromParentClosureScopedFromChild(): void
    {
        $mock = new ChildClass();
        $realName = 'foo';
        $serializedName = 'foo_bar';
        $type = new TypeToken(stdClass::class);
        $getter = new GetByClosure('bar', ChildClass::class);
        $setter = new SetByClosure('bar', ChildClass::class);

        $property = new Property(
            $realName,
            $serializedName,
            $type,
            $getter,
            $setter,
            new AnnotationCollection(),
            0,
            false,
            $this->classMetadata
        );
        $this->propertyCollection->add($property);

        $property->set($mock, 'bar');
        self::assertSame('bar', $property->get($mock));
    }

    public function testSetNull(): void
    {
        $mock = new class { public $foo; };
        $realName = 'foo';
        $serializedName = 'foo_bar';
        $type = new TypeToken(stdClass::class);

        $property = new Property(
            $realName,
            $serializedName,
            $type,
            new GetByPublicProperty('foo'),
            new SetByPublicProperty('foo'),
            new AnnotationCollection(),
            0,
            false,
            $this->classMetadata
        );
        $this->propertyCollection->add($property);

        $property->set($mock, null);
        self::assertNull($property->get($mock));
    }
}
