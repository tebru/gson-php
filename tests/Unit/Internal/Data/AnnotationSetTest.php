<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\Data;

use PHPUnit_Framework_TestCase;
use Tebru\Gson\Internal\Data\AnnotationSet;
use Tebru\Gson\Test\Mock\Annotation\BarAnnotation;
use Tebru\Gson\Test\Mock\Annotation\FooAnnotation;

/**
 * Class AnnotationSetTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\Data\AnnotationSet
 */
class AnnotationSetTest extends PHPUnit_Framework_TestCase
{
    public function testGetAnnotation()
    {
        $annotation = new FooAnnotation(['value' => 'foo']);
        $set = new AnnotationSet([$annotation]);

        self::assertSame($annotation, $set->getAnnotation(FooAnnotation::class));
    }

    public function testGetAnnotationDoesNotExist()
    {
        $set = new AnnotationSet();

        self::assertNull($set->getAnnotation(FooAnnotation::class));
    }

    public function testAddAnnotation()
    {
        $annotation = new FooAnnotation(['value' => 'foo']);
        $set = new AnnotationSet();
        $set->add($annotation);

        self::assertSame($annotation, $set->getAnnotation(FooAnnotation::class));
    }

    public function testAddSameAnnotation()
    {
        $annotation = new FooAnnotation(['value' => 'foo']);
        $set = new AnnotationSet();
        $set->add($annotation);
        $set->add(new FooAnnotation(['value' => 'foo2']));

        self::assertSame($annotation, $set->getAnnotation(FooAnnotation::class));
    }

    public function testClear()
    {
        $set = new AnnotationSet([new FooAnnotation(['value' => 'foo'])]);
        $set->clear();

        self::assertCount(0, $set);
    }

    public function testContainsTrue()
    {
        $annotation = new FooAnnotation(['value' => 'foo']);
        $set = new AnnotationSet([$annotation]);

        self::assertTrue($set->contains(new FooAnnotation(['value' => 'foo'])));
    }

    public function testContainsFalse()
    {
        $annotation = new FooAnnotation(['value' => 'foo']);
        $set = new AnnotationSet([$annotation]);

        self::assertFalse($set->contains(new BarAnnotation(['value' => 'bar'])));
    }

    public function testRemove()
    {
        $annotation = new FooAnnotation(['value' => 'foo']);
        $set = new AnnotationSet([$annotation]);
        $removed = $set->remove($annotation);

        self::assertTrue($removed);
        self::assertCount(0, $set);
    }

    public function testRemoveFalse()
    {
        $annotation = new FooAnnotation(['value' => 'foo']);
        $set = new AnnotationSet([$annotation]);
        $removed = $set->remove(new BarAnnotation(['value' => 'foo']));

        self::assertFalse($removed);
        self::assertCount(1, $set);
    }

    public function testToArray()
    {
        $annotation = new FooAnnotation(['value' => 'foo']);
        $set = new AnnotationSet([$annotation]);

        self::assertSame([$annotation], $set->toArray());
    }

    public function testCanIterate()
    {
        $annotation = new FooAnnotation(['value' => 'foo']);
        $set = new AnnotationSet([$annotation]);

        foreach ($set as $element) {
            self::assertSame($annotation, $element);
        }
    }
}
