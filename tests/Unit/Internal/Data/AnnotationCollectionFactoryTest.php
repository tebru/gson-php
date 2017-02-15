<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\Data;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\VoidCache;
use PHPUnit_Framework_TestCase;
use Tebru\Gson\Annotation\SerializedName;
use Tebru\Gson\Annotation\VirtualProperty;
use Tebru\Gson\Internal\Data\AnnotationCollectionFactory;
use Tebru\Gson\Internal\Data\AnnotationSet;
use Tebru\Gson\Test\Mock\Annotation\BarAnnotation;
use Tebru\Gson\Test\Mock\Annotation\BazAnnotation;
use Tebru\Gson\Test\Mock\Annotation\FooAnnotation;
use Tebru\Gson\Test\Mock\Annotation\QuxAnnotation;
use Tebru\Gson\Test\Mock\ChildClass;
use Tebru\Gson\Test\Mock\ClassWithoutParent;

/**
 * Class AnnotationCollectionFactoryTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\Data\AnnotationCollectionFactory
 */
class AnnotationCollectionFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testCreateWithoutParents()
    {
        $factory = new AnnotationCollectionFactory(new AnnotationReader(), new VoidCache());
        $annotations = $factory->createPropertyAnnotations(ClassWithoutParent::class, 'foo');

        $expected = [
            new FooAnnotation(['value' => 'foo']),
            new BarAnnotation(['value' => 'bar']),
        ];

        self::assertEquals($expected, $annotations->toArray(AnnotationSet::TYPE_PROPERTY));
    }

    public function testCreateWithParents()
    {
        $factory = new AnnotationCollectionFactory(new AnnotationReader(), new VoidCache());
        $annotations = $factory->createPropertyAnnotations(ChildClass::class, 'foo');

        $expected = [
            new FooAnnotation(['value' => 'foo']),
            new BarAnnotation(['value' => 'bar']),
            new QuxAnnotation(['value' => 'qux']),
        ];

        self::assertEquals($expected, $annotations->toArray(AnnotationSet::TYPE_PROPERTY));
    }

    public function testCreateTwoLevels()
    {
        $factory = new AnnotationCollectionFactory(new AnnotationReader(), new VoidCache());
        $annotations = $factory->createPropertyAnnotations(ChildClass::class, 'qux');

        $expected = [
            new QuxAnnotation(['value' => 'qux']),
        ];

        self::assertEquals($expected, $annotations->toArray(AnnotationSet::TYPE_PROPERTY));
    }

    public function testCreatePropertyAnnotationsUsesCache()
    {
        $cachedAnnotations = new AnnotationSet();
        $cachedAnnotations->addAnnotation(new FooAnnotation([]), AnnotationSet::TYPE_PROPERTY);

        $cache = new ArrayCache();

        $cache->save(ChildClass::class.':'.'foo', $cachedAnnotations);
        $factory = new AnnotationCollectionFactory(new AnnotationReader(), $cache);
        $annotations = $factory->createPropertyAnnotations(ChildClass::class, 'foo');

        self::assertSame($cachedAnnotations, $annotations);
    }

    public function testCreateClassAnnotations()
    {
        $factory = new AnnotationCollectionFactory(new AnnotationReader(), new VoidCache());
        $annotations = $factory->createClassAnnotations(ChildClass::class);

        $expected = [
            new FooAnnotation(['value' => 'foo3']),
            new BazAnnotation(['value' => 'baz']),
            new BarAnnotation(['value' => 'bar2']),
        ];

        self::assertEquals($expected, $annotations->toArray(AnnotationSet::TYPE_CLASS));
    }

    public function testCreateClassAnnotationsUsesCache()
    {
        $cachedAnnotations = new AnnotationSet();
        $cachedAnnotations->addAnnotation(new FooAnnotation([]), AnnotationSet::TYPE_CLASS);

        $cache = new ArrayCache();

        $cache->save(ChildClass::class, $cachedAnnotations);
        $factory = new AnnotationCollectionFactory(new AnnotationReader(), $cache);
        $annotations = $factory->createClassAnnotations(ChildClass::class);

        self::assertSame($cachedAnnotations, $annotations);
    }

    public function testCreateMethodAnnotations()
    {
        $factory = new AnnotationCollectionFactory(new AnnotationReader(), new VoidCache());
        $annotations = $factory->createMethodAnnotations(ChildClass::class, 'virtualProperty');

        $expected = [
            new VirtualProperty(),
            new SerializedName(['value' => 'new_virtual_property']),
        ];

        self::assertEquals($expected, $annotations->toArray(AnnotationSet::TYPE_METHOD));
    }

    public function testCreateMethodAnnotationsUsesCache()
    {
        $cachedAnnotations = new AnnotationSet();
        $cachedAnnotations->addAnnotation(new VirtualProperty(), AnnotationSet::TYPE_METHOD);

        $cache = new ArrayCache();
        $cache->save(ChildClass::class.':'.'virtualProperty', $cachedAnnotations);

        $factory = new AnnotationCollectionFactory(new AnnotationReader(), $cache);
        $annotations = $factory->createMethodAnnotations(ChildClass::class, 'virtualProperty');

        self::assertSame($cachedAnnotations, $annotations);
    }
}
