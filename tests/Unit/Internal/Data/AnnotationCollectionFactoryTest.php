<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\Data;

use Doctrine\Common\Annotations\AnnotationReader;
use PHPUnit_Framework_TestCase;
use ReflectionProperty;
use Tebru\Gson\Internal\Data\AnnotationCollectionFactory;
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
 */
class AnnotationCollectionFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testCreateWithoutParents()
    {
        $factory = new AnnotationCollectionFactory(new AnnotationReader());
        $annotations = $factory->create(new ReflectionProperty(ClassWithoutParent::class, 'foo'));

        $expected = [
            new FooAnnotation(['value' => 'foo']),
            new BarAnnotation(['value' => 'bar']),
            new BazAnnotation(['value' => 'baz']),
        ];

        self::assertEquals($expected, $annotations->toArray());
    }

    public function testCreateWithParents()
    {
        $factory = new AnnotationCollectionFactory(new AnnotationReader());
        $annotations = $factory->create(new ReflectionProperty(ChildClass::class, 'foo'));

        $expected = [
            new FooAnnotation(['value' => 'foo']),
            new BarAnnotation(['value' => 'bar']),
            new BazAnnotation(['value' => 'baz']),
            new QuxAnnotation(['value' => 'qux']),
        ];

        self::assertEquals($expected, $annotations->toArray());
    }

    public function testCreateTwoLevels()
    {
        $factory = new AnnotationCollectionFactory(new AnnotationReader());
        $annotations = $factory->create(new ReflectionProperty(ChildClass::class, 'qux'));

        $expected = [
            new QuxAnnotation(['value' => 'qux']),
        ];

        self::assertEquals($expected, $annotations->toArray());
    }
}
