<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\Data;

use PHPUnit\Framework\TestCase;
use stdClass;
use Tebru\AnnotationReader\AnnotationCollection;
use Tebru\Gson\Internal\AccessorStrategy\GetByPublicProperty;
use Tebru\Gson\Internal\AccessorStrategy\SetByPublicProperty;
use Tebru\Gson\Internal\Data\PropertyCollection;
use Tebru\Gson\Internal\Data\Property;
use Tebru\Gson\Test\MockProvider;
use Tebru\PhpType\TypeToken;

/**
 * Class PropertyCollectionTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\Data\PropertyCollection
 */
class PropertyCollectionTest extends TestCase
{
    /**
     * @var Property
     */
    private $property;

    /**
     * @var PropertyCollection
     */
    private $propertyCollection;

    public function setUp()
    {
        $realName = 'foo';
        $serializedName = 'foo_bar';
        $type = new TypeToken(stdClass::class);

        $this->propertyCollection = new PropertyCollection();
        $this->property = new Property(
            $realName,
            $serializedName,
            $type,
            new GetByPublicProperty('foo'),
            new SetByPublicProperty('foo'),
            new AnnotationCollection(),
            0,
            false,
            MockProvider::classMetadata(stdClass::class, $this->propertyCollection)
        );
        $this->propertyCollection->add($this->property);
    }

    public function testGetters(): void
    {
        self::assertSame($this->property, $this->propertyCollection->getBySerializedName('foo_bar'));
    }

    public function testGettersNotFound(): void
    {
        self::assertNull($this->propertyCollection->getBySerializedName('foo_bar2'));
    }

    public function testToArray(): void
    {
        self::assertSame([$this->property], $this->propertyCollection->toArray());
    }

    public function testIterate(): void
    {
        foreach ($this->propertyCollection as $p) {
            self::assertSame($this->property, $p);
        }
    }
}
