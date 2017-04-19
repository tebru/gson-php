<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\Data;

use InvalidArgumentException;
use PHPUnit_Framework_TestCase;
use Tebru\Gson\Annotation\Since;
use Tebru\Gson\Annotation\Type;
use Tebru\Gson\Annotation\Until;
use Tebru\Gson\Internal\Data\AnnotationSet;

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
        $classAnnotation = new Since(['value' => '1']);
        $propertyAnnotation = new Until(['value' => '2']);
        $methodAnnotation = new Type(['value' => 'int']);

        $set = new AnnotationSet();
        $set->addAnnotation($classAnnotation, AnnotationSet::TYPE_CLASS);
        $set->addAnnotation($propertyAnnotation, AnnotationSet::TYPE_PROPERTY);
        $set->addAnnotation($methodAnnotation, AnnotationSet::TYPE_METHOD);

        self::assertSame($classAnnotation, $set->getAnnotation(Since::class, AnnotationSet::TYPE_CLASS));
        self::assertSame($propertyAnnotation, $set->getAnnotation(Until::class, AnnotationSet::TYPE_PROPERTY));
        self::assertSame($methodAnnotation, $set->getAnnotation(Type::class, AnnotationSet::TYPE_METHOD));
    }

    public function testGetAnnotationByTypeNull()
    {
        $classAnnotation = new Since(['value' => '1']);
        $propertyAnnotation = new Until(['value' => '2']);
        $methodAnnotation = new Type(['value' => 'int']);

        $set = new AnnotationSet();
        $set->addAnnotation($classAnnotation, AnnotationSet::TYPE_CLASS);
        $set->addAnnotation($propertyAnnotation, AnnotationSet::TYPE_PROPERTY);
        $set->addAnnotation($methodAnnotation, AnnotationSet::TYPE_METHOD);

        self::assertNull($set->getAnnotation(Type::class, AnnotationSet::TYPE_CLASS));
        self::assertNull($set->getAnnotation(Since::class, AnnotationSet::TYPE_PROPERTY));
        self::assertNull($set->getAnnotation(Until::class, AnnotationSet::TYPE_METHOD));
    }

    public function testAddMultipleAnnotationOfSameType()
    {
        $classAnnotation = new Since(['value' => '1']);
        $propertyAnnotation = new Until(['value' => '2']);
        $methodAnnotation = new Type(['value' => 'int']);

        $set = new AnnotationSet();
        $set->addAnnotation($classAnnotation, AnnotationSet::TYPE_CLASS);
        $set->addAnnotation(new Since(['value' => '1']), AnnotationSet::TYPE_CLASS);
        $set->addAnnotation($propertyAnnotation, AnnotationSet::TYPE_PROPERTY);
        $set->addAnnotation(new Until(['value' => '2']), AnnotationSet::TYPE_PROPERTY);
        $set->addAnnotation($methodAnnotation, AnnotationSet::TYPE_METHOD);
        $set->addAnnotation(new Type(['value' => 'int']), AnnotationSet::TYPE_METHOD);

        self::assertSame($classAnnotation, $set->getAnnotation(Since::class, AnnotationSet::TYPE_CLASS));
        self::assertSame($propertyAnnotation, $set->getAnnotation(Until::class, AnnotationSet::TYPE_PROPERTY));
        self::assertSame($methodAnnotation, $set->getAnnotation(Type::class, AnnotationSet::TYPE_METHOD));
    }

    public function testSameAnnotationToDifferentTypes()
    {
        $classAnnotation = new Since(['value' => '1']);
        $propertyAnnotation = new Since(['value' => '2']);
        $methodAnnotation = new Since(['value' => '3']);

        $set = new AnnotationSet();
        $set->addAnnotation($classAnnotation, AnnotationSet::TYPE_CLASS);
        $set->addAnnotation($propertyAnnotation, AnnotationSet::TYPE_PROPERTY);
        $set->addAnnotation($methodAnnotation, AnnotationSet::TYPE_METHOD);

        self::assertSame($classAnnotation, $set->getAnnotation(Since::class, AnnotationSet::TYPE_CLASS));
        self::assertSame($propertyAnnotation, $set->getAnnotation(Since::class, AnnotationSet::TYPE_PROPERTY));
        self::assertSame($methodAnnotation, $set->getAnnotation(Since::class, AnnotationSet::TYPE_METHOD));
    }

    public function testWillLookInMultiplePlaces()
    {
        $classAnnotation = new Since(['value' => '1']);
        $propertyAnnotation = new Until(['value' => '2']);
        $methodAnnotation = new Since(['value' => 'int']);

        $set = new AnnotationSet();
        $set->addAnnotation($classAnnotation, AnnotationSet::TYPE_CLASS);
        $set->addAnnotation($propertyAnnotation, AnnotationSet::TYPE_PROPERTY);
        $set->addAnnotation($methodAnnotation, AnnotationSet::TYPE_METHOD);

        self::assertSame($propertyAnnotation, $set->getAnnotation(Until::class, AnnotationSet::TYPE_CLASS | AnnotationSet::TYPE_PROPERTY));
        self::assertSame($classAnnotation, $set->getAnnotation(Since::class, AnnotationSet::TYPE_CLASS |AnnotationSet::TYPE_PROPERTY));
        self::assertNull($set->getAnnotation(Until::class, AnnotationSet::TYPE_CLASS));
    }

    public function testCannotAddAnnotationWithInvalidType()
    {

        $set = new AnnotationSet();
        try {
            $set->addAnnotation(new Since(['value' => '1']), 10);
        } catch (InvalidArgumentException $exception) {
            self::assertSame('Type not supported', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testToArray()
    {
        $classAnnotation = new Since(['value' => '1']);
        $propertyAnnotation = new Until(['value' => '2']);
        $methodAnnotation = new Type(['value' => 'int']);

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
        $set = new AnnotationSet();
        try {
            $set->toArray(10);
        } catch (InvalidArgumentException $exception) {
            self::assertSame('Type not supported', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }
}
