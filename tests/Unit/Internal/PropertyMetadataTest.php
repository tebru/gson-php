<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
namespace Tebru\Gson\Test\Unit\Internal;

use PHPUnit_Framework_TestCase;
use ReflectionProperty;
use Tebru\AnnotationReader\AnnotationCollection;
use Tebru\Gson\Internal\DefaultClassMetadata;
use Tebru\Gson\Internal\DefaultPropertyMetadata;
use Tebru\Gson\PropertyMetadata;
use Tebru\Gson\Test\Mock\Annotation\FooAnnotation;
use Tebru\Gson\Test\Mock\Foo;
use Tebru\PhpType\TypeToken;

/**
 * Class PropertyMetadataTest
 *
 * @author Nate Brunette <n@tebru.net>
 */
class PropertyMetadataTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var DefaultClassMetadata
     */
    private $classMetadata;

    /**
     * @var AnnotationCollection
     */
    private $annotations;

    /**
     * @var PropertyMetadata
     */
    private $propertyMetadata;

    /**
     * @var PropertyMetadata
     */
    private $virtualPropertyMetadata;

    public function setUp()
    {
        $this->classMetadata = new DefaultClassMetadata(Foo::class, new AnnotationCollection());
        $this->annotations = new AnnotationCollection();
        $this->propertyMetadata = new DefaultPropertyMetadata(
            'foo',
            'foo',
            new TypeToken('string'),
            ReflectionProperty::IS_PUBLIC,
            $this->classMetadata,
            $this->annotations,
            false
        );

        $this->virtualPropertyMetadata = new DefaultPropertyMetadata(
            'foo2',
            'foo2',
            new TypeToken('string'),
            ReflectionProperty::IS_PUBLIC,
            $this->classMetadata,
            $this->annotations,
            true
        );
    }

    public function testGetters()
    {
        self::assertSame('foo', $this->propertyMetadata->getName());
        self::assertSame('foo', $this->propertyMetadata->getSerializedName());
        self::assertSame('string', (string) $this->propertyMetadata->getType());
        self::assertSame('string', $this->propertyMetadata->getTypeName());
        self::assertSame(ReflectionProperty::IS_PUBLIC, $this->propertyMetadata->getModifiers());
        self::assertSame($this->classMetadata, $this->propertyMetadata->getDeclaringClassMetadata());
        self::assertSame(Foo::class, $this->propertyMetadata->getDeclaringClassName());
        self::assertSame($this->annotations, $this->propertyMetadata->getAnnotations());
        self::assertSame($this->propertyMetadata, $this->classMetadata->getPropertyMetadata()[0]);
        self::assertSame($this->virtualPropertyMetadata, $this->classMetadata->getPropertyMetadata()[1]);
        self::assertSame($this->propertyMetadata, $this->classMetadata->getProperty('foo'));
        self::assertSame($this->virtualPropertyMetadata, $this->classMetadata->getProperty('foo2'));
        self::assertNull($this->propertyMetadata->getDeclaringClassMetadata()->getProperty('foo3'));
        self::assertFalse($this->propertyMetadata->isVirtual());
    }

    public function testGetAnnotation()
    {
        $annotation = new FooAnnotation([]);
        $this->annotations->add($annotation);

        self::assertSame($annotation, $this->propertyMetadata->getAnnotation(FooAnnotation::class));
    }

    public function testGetAnnotationNull()
    {
        self::assertNull($this->propertyMetadata->getAnnotation(FooAnnotation::class));
    }

    public function testGetAnnotationVirtual()
    {
        $annotation = new FooAnnotation([]);
        $this->annotations->add($annotation);


        self::assertSame($annotation, $this->virtualPropertyMetadata->getAnnotation(FooAnnotation::class));
    }

    public function testGetAnnotationVirtualNull()
    {
        self::assertNull($this->virtualPropertyMetadata->getAnnotation(FooAnnotation::class));
    }
}
