<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\Data;

use PHPUnit_Framework_TestCase;
use Tebru\Gson\Internal\AccessorStrategy\GetByPublicProperty;
use Tebru\Gson\Internal\AccessorStrategy\SetByPublicProperty;
use Tebru\Gson\Internal\Data\AnnotationSet;
use Tebru\Gson\Internal\Data\PropertyCollection;
use Tebru\Gson\Internal\DefaultPhpType;
use Tebru\Gson\Internal\Data\Property;

/**
 * Class PropertyCollectionTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\Data\PropertyCollection
 */
class PropertyCollectionTest extends PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $realName = 'foo';
        $serializedName = 'foo_bar';
        $type = new DefaultPhpType('Foo');

        $property = new Property(
            $realName,
            $serializedName,
            $type,
            new GetByPublicProperty('foo'),
            new SetByPublicProperty('foo'),
            new AnnotationSet(),
            0,
            false
        );
        $propertyCollection = new PropertyCollection([$property]);

        self::assertSame($property, $propertyCollection->getBySerializedName('foo_bar'));
    }

    public function testGettersNotFound()
    {
        $realName = 'foo';
        $serializedName = 'foo_bar';
        $type = new DefaultPhpType('Foo');

        $property = new Property(
            $realName,
            $serializedName,
            $type,
            new GetByPublicProperty('foo'),
            new SetByPublicProperty('foo'),
            new AnnotationSet(),
            0,
            false
        );
        $propertyCollection = new PropertyCollection([$property]);

        self::assertNull($propertyCollection->getBySerializedName('foo_bar2'));
    }

    public function testToArray()
    {
        $realName = 'foo';
        $serializedName = 'foo_bar';
        $type = new DefaultPhpType('Foo');

        $property = new Property(
            $realName,
            $serializedName,
            $type,
            new GetByPublicProperty('foo'),
            new SetByPublicProperty('foo'),
            new AnnotationSet(),
            0,
            false
        );
        $propertyCollection = new PropertyCollection([$property]);

        self::assertSame([$property], $propertyCollection->toArray());
    }

    public function testIterate()
    {
        $realName = 'foo';
        $serializedName = 'foo_bar';
        $type = new DefaultPhpType('Foo');

        $property = new Property(
            $realName,
            $serializedName,
            $type,
            new GetByPublicProperty('foo'),
            new SetByPublicProperty('foo'),
            new AnnotationSet(),
            0,
            false
        );
        $propertyCollection = new PropertyCollection([$property]);

        foreach ($propertyCollection as $p) {
            self::assertSame($property, $p);
        }
    }
}
