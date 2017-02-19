<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
namespace Tebru\Gson\Test\Unit\Internal\Data;

use PHPUnit_Framework_TestCase;
use ReflectionProperty;
use Tebru\Gson\ClassMetadata;
use Tebru\Gson\Internal\Data\AnnotationSet;
use Tebru\Gson\Internal\Data\MetadataPropertyCollection;
use Tebru\Gson\PhpType;
use Tebru\Gson\PropertyMetadata;
use Tebru\Gson\Test\Mock\Foo;

/**
 * Class MetadataPropertyCollectionTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\Data\MetadataPropertyCollection
 */
class MetadataPropertyCollectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var MetadataPropertyCollection
     */
    private $metadataPropertyCollection;

    /**
     * @var PropertyMetadata
     */
    private $defaultPropertyMetadata;

    public function setUp()
    {
        $this->metadataPropertyCollection = new MetadataPropertyCollection();
        $this->defaultPropertyMetadata = new PropertyMetadata(
            'foo',
            'foo',
            new PhpType('string'),
            ReflectionProperty::IS_PRIVATE,
            new ClassMetadata(Foo::class, new AnnotationSet()),
            new AnnotationSet(),
            false
        );
    }

    public function testGetProperty()
    {
        $this->metadataPropertyCollection->add($this->defaultPropertyMetadata);

        self::assertSame($this->defaultPropertyMetadata, $this->metadataPropertyCollection->get('foo'));
    }

    public function testGetPropertyInvalidName()
    {
        $this->metadataPropertyCollection->add($this->defaultPropertyMetadata);

        self::assertNull($this->metadataPropertyCollection->get('foo2'));
    }

    public function testGetPropertyEmptyCollection()
    {
        self::assertNull($this->metadataPropertyCollection->get('foo'));
    }
}
