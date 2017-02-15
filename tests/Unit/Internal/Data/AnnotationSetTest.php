<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\Data;

use InvalidArgumentException;
use PHPUnit_Framework_TestCase;
use Tebru\Gson\Internal\Data\AnnotationSet;
use Tebru\Gson\Test\Mock\Annotation\BarAnnotation;
use Tebru\Gson\Test\Mock\Annotation\BazAnnotation;
use Tebru\Gson\Test\Mock\Annotation\FooAnnotation;

/**
 * Class AnnotationSetTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\Data\AnnotationSet
 */
class AnnotationSetTest extends PHPUnit_Framework_TestCase
{
    public function testGetAnnotationByType()
    {
        $classAnnotation = new FooAnnotation(['value' => 'foo']);
        $propertyAnnotation = new BarAnnotation(['value' => 'foo']);
        $methodAnnotation = new BazAnnotation(['value' => 'foo']);

        $set = new AnnotationSet();
        $set->addAnnotation($classAnnotation, AnnotationSet::TYPE_CLASS);
        $set->addAnnotation($propertyAnnotation, AnnotationSet::TYPE_PROPERTY);
        $set->addAnnotation($methodAnnotation, AnnotationSet::TYPE_METHOD);

        self::assertSame($classAnnotation, $set->getAnnotation(FooAnnotation::class, AnnotationSet::TYPE_CLASS));
        self::assertSame($propertyAnnotation, $set->getAnnotation(BarAnnotation::class, AnnotationSet::TYPE_PROPERTY));
        self::assertSame($methodAnnotation, $set->getAnnotation(BazAnnotation::class, AnnotationSet::TYPE_METHOD));
    }

    public function testGetAnnotationByTypeNull()
    {
        $classAnnotation = new FooAnnotation(['value' => 'foo']);
        $propertyAnnotation = new BarAnnotation(['value' => 'foo']);
        $methodAnnotation = new BazAnnotation(['value' => 'foo']);

        $set = new AnnotationSet();
        $set->addAnnotation($classAnnotation, AnnotationSet::TYPE_CLASS);
        $set->addAnnotation($propertyAnnotation, AnnotationSet::TYPE_PROPERTY);
        $set->addAnnotation($methodAnnotation, AnnotationSet::TYPE_METHOD);

        self::assertNull($set->getAnnotation(BazAnnotation::class, AnnotationSet::TYPE_CLASS));
        self::assertNull($set->getAnnotation(FooAnnotation::class, AnnotationSet::TYPE_PROPERTY));
        self::assertNull($set->getAnnotation(BarAnnotation::class, AnnotationSet::TYPE_METHOD));
    }

    public function testAddMultipleAnnotationOfSameType()
    {
        $classAnnotation = new FooAnnotation(['value' => 'foo']);
        $propertyAnnotation = new BarAnnotation(['value' => 'foo']);
        $methodAnnotation = new BazAnnotation(['value' => 'foo']);

        $set = new AnnotationSet();
        $set->addAnnotation($classAnnotation, AnnotationSet::TYPE_CLASS);
        $set->addAnnotation(new FooAnnotation(['value' => 'foo2']), AnnotationSet::TYPE_CLASS);
        $set->addAnnotation($propertyAnnotation, AnnotationSet::TYPE_PROPERTY);
        $set->addAnnotation(new BarAnnotation(['value' => 'foo2']), AnnotationSet::TYPE_PROPERTY);
        $set->addAnnotation($methodAnnotation, AnnotationSet::TYPE_METHOD);
        $set->addAnnotation(new BazAnnotation(['value' => 'foo2']), AnnotationSet::TYPE_METHOD);

        self::assertSame($classAnnotation, $set->getAnnotation(FooAnnotation::class, AnnotationSet::TYPE_CLASS));
        self::assertSame($propertyAnnotation, $set->getAnnotation(BarAnnotation::class, AnnotationSet::TYPE_PROPERTY));
        self::assertSame($methodAnnotation, $set->getAnnotation(BazAnnotation::class, AnnotationSet::TYPE_METHOD));
    }

    public function testSameAnnotationToDifferentTypes()
    {
        $classAnnotation = new FooAnnotation(['value' => 'foo']);
        $propertyAnnotation = new FooAnnotation(['value' => 'foo2']);
        $methodAnnotation = new FooAnnotation(['value' => 'foo3']);

        $set = new AnnotationSet();
        $set->addAnnotation($classAnnotation, AnnotationSet::TYPE_CLASS);
        $set->addAnnotation($propertyAnnotation, AnnotationSet::TYPE_PROPERTY);
        $set->addAnnotation($methodAnnotation, AnnotationSet::TYPE_METHOD);

        self::assertSame($classAnnotation, $set->getAnnotation(FooAnnotation::class, AnnotationSet::TYPE_CLASS));
        self::assertSame($propertyAnnotation, $set->getAnnotation(FooAnnotation::class, AnnotationSet::TYPE_PROPERTY));
        self::assertSame($methodAnnotation, $set->getAnnotation(FooAnnotation::class, AnnotationSet::TYPE_METHOD));
    }

    public function testWillLookInMultiplePlaces()
    {
        $classAnnotation = new FooAnnotation(['value' => 'foo']);
        $propertyAnnotation = new BarAnnotation(['value' => 'foo']);
        $methodAnnotation = new FooAnnotation(['value' => 'foo']);

        $set = new AnnotationSet();
        $set->addAnnotation($classAnnotation, AnnotationSet::TYPE_CLASS);
        $set->addAnnotation($propertyAnnotation, AnnotationSet::TYPE_PROPERTY);
        $set->addAnnotation($methodAnnotation, AnnotationSet::TYPE_METHOD);

        self::assertSame($propertyAnnotation, $set->getAnnotation(BarAnnotation::class, AnnotationSet::TYPE_CLASS | AnnotationSet::TYPE_PROPERTY));
        self::assertSame($classAnnotation, $set->getAnnotation(FooAnnotation::class, AnnotationSet::TYPE_CLASS |AnnotationSet::TYPE_PROPERTY));
        self::assertNull($set->getAnnotation(BarAnnotation::class, AnnotationSet::TYPE_CLASS));
    }

    public function testCannotAddAnnotationWithInvalidType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Type not supported');

        $set = new AnnotationSet();
        $set->addAnnotation(new FooAnnotation([]), 10);
    }

    public function testToArray()
    {
        $classAnnotation = new FooAnnotation(['value' => 'foo']);
        $propertyAnnotation = new BarAnnotation(['value' => 'foo']);
        $methodAnnotation = new BazAnnotation(['value' => 'foo']);

        $set = new AnnotationSet();
        $set->addAnnotation($classAnnotation, AnnotationSet::TYPE_CLASS);
        $set->addAnnotation($propertyAnnotation, AnnotationSet::TYPE_PROPERTY);
        $set->addAnnotation($methodAnnotation, AnnotationSet::TYPE_METHOD);

        self::assertSame([$classAnnotation], $set->toArray(AnnotationSet::TYPE_CLASS));
        self::assertSame([$propertyAnnotation], $set->toArray(AnnotationSet::TYPE_PROPERTY));
        self::assertSame([$methodAnnotation], $set->toArray(AnnotationSet::TYPE_METHOD));
    }

    public function testCannotCallToArrayWithInvalidType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Type not supported');

        $set = new AnnotationSet();
        $set->toArray(10);

    }
}
