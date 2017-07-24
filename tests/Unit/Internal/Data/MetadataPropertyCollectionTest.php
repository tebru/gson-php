<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
namespace Tebru\Gson\Test\Unit\Internal\Data;

use PHPUnit_Framework_TestCase;
use ReflectionProperty;
use Tebru\AnnotationReader\AnnotationCollection;
use Tebru\Gson\Internal\DefaultClassMetadata;
use Tebru\Gson\Internal\Data\MetadataPropertyCollection;
use Tebru\Gson\Internal\DefaultPropertyMetadata;
use Tebru\Gson\PropertyMetadata;
use Tebru\Gson\Test\Mock\Foo;
use Tebru\PhpType\TypeToken;

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
        $this->defaultPropertyMetadata = new DefaultPropertyMetadata(
            'foo',
            'foo',
            new TypeToken('string'),
            ReflectionProperty::IS_PRIVATE,
            new DefaultClassMetadata(Foo::class, new AnnotationCollection()),
            new AnnotationCollection(),
            false
        );
    }

    public function testGetProperty()
    {
        $this->metadataPropertyCollection->add($this->defaultPropertyMetadata);

        self::assertSame($this->defaultPropertyMetadata, $this->metadataPropertyCollection->get('foo'));
    }
}
