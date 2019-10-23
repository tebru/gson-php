<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal;

use PHPUnit\Framework\TestCase;
use Tebru\AnnotationReader\AnnotationCollection;
use Tebru\Gson\Internal\AccessorStrategy\GetByPublicProperty;
use Tebru\Gson\Internal\AccessorStrategy\SetByPublicProperty;
use Tebru\Gson\Internal\Data\Property;
use Tebru\Gson\Internal\Data\PropertyCollection;
use Tebru\Gson\Internal\DefaultClassMetadata;
use Tebru\Gson\Test\Mock\Annotation\FooAnnotation;
use Tebru\Gson\Test\Mock\Foo;
use Tebru\PhpType\TypeToken;

/**
 * Class ClassMetadataTest
 *
 * @author Nate Brunette <n@tebru.net>
 *
 * @covers \Tebru\Gson\Internal\DefaultClassMetadata
 */
class ClassMetadataTest extends TestCase
{
    /**
     * @var AnnotationCollection
     */
    private $annotations;

    /**
     * @var PropertyCollection
     */
    private $propertyCollection;

    /**
     * @var DefaultClassMetadata
     */
    private $metadata;

    public function setUp()
    {
        $this->annotations = new AnnotationCollection();
        $this->propertyCollection = new PropertyCollection();
        $this->metadata = new DefaultClassMetadata(Foo::class, $this->annotations, $this->propertyCollection);
    }

    public function testClassMetadata(): void
    {
        self::assertSame(Foo::class, $this->metadata->getName());
        self::assertSame($this->annotations, $this->metadata->getAnnotations());
    }

    public function testPropertyMetadata(): void
    {
        $this->propertyCollection->add(new Property(
            'foo',
            'foo',
            TypeToken::create('string'),
            new GetByPublicProperty('foo'),
            new SetByPublicProperty('foo'),
            new AnnotationCollection(),
            0,
            false,
            $this->metadata
        ));
        self::assertCount(1, $this->metadata->getPropertyMetadata());
        self::assertCount(1, $this->metadata->getPropertyMetadataCollection()->toArray());
    }

    public function testProperty(): void
    {
        $property = new Property(
            'foo',
            'foo',
            TypeToken::create('string'),
            new GetByPublicProperty('foo'),
            new SetByPublicProperty('foo'),
            new AnnotationCollection(),
            0,
            false,
            $this->metadata
        );
        $this->propertyCollection->add($property);
        self::assertSame($property, $this->metadata->getProperty('foo'));
    }

    public function testPropertyNull(): void
    {
        self::assertNull($this->metadata->getProperty('foo'));
    }

    public function testPropertyCollection(): void
    {
        $property = new Property(
            'foo',
            'foo',
            TypeToken::create('string'),
            new GetByPublicProperty('foo'),
            new SetByPublicProperty('foo'),
            new AnnotationCollection(),
            0,
            false,
            $this->metadata
        );
        $this->propertyCollection->add($property);
        self::assertSame($this->propertyCollection, $this->metadata->getPropertyCollection());
    }

    public function testGetAnnotation(): void
    {
        $annotation = new FooAnnotation([]);
        $this->annotations->add($annotation);

        self::assertSame($annotation, $this->metadata->getAnnotation(FooAnnotation::class));
    }

    public function testGetAnnotationNull(): void
    {
        self::assertNull($this->metadata->getAnnotation(FooAnnotation::class));
    }

    public function testSkipSerialize(): void
    {
        $property = new Property(
            'foo',
            'foo',
            TypeToken::create('string'),
            new GetByPublicProperty('foo'),
            new SetByPublicProperty('foo'),
            new AnnotationCollection(),
            0,
            false,
            $this->metadata
        );
        $this->propertyCollection->add($property);
        self::assertFalse($this->metadata->skipSerialize());
        self::assertFalse($this->metadata->skipDeserialize());
    }
}
