<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal;

use PHPUnit_Framework_TestCase;
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
 */
class ClassMetadataTest extends PHPUnit_Framework_TestCase
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

    public function testClassMetadata()
    {
        self::assertSame(Foo::class, $this->metadata->getName());
        self::assertSame($this->annotations, $this->metadata->getAnnotations());
    }

    public function testPropertyMetadata()
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
    }

    public function testProperty()
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

    public function testPropertyNull()
    {
        self::assertNull($this->metadata->getProperty('foo'));
    }

    public function testGetAnnotation()
    {
        $annotation = new FooAnnotation([]);
        $this->annotations->add($annotation);

        self::assertSame($annotation, $this->metadata->getAnnotation(FooAnnotation::class));
    }

    public function testGetAnnotationNull()
    {
        self::assertNull($this->metadata->getAnnotation(FooAnnotation::class));
    }
}
