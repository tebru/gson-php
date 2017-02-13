<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\Naming;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\VoidCache;
use PHPUnit_Framework_TestCase;
use ReflectionProperty;
use Tebru\Gson\Internal\Data\AnnotationCollectionFactory;
use Tebru\Gson\Internal\Naming\PropertyNamer;
use Tebru\Gson\Internal\Naming\SnakePropertyNamingStrategy;
use Tebru\Gson\Test\Mock\AnnotatedMock;

/**
 * Class PropertyNamerTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\Naming\PropertyNamer
 */
class PropertyNamerTest extends PHPUnit_Framework_TestCase
{
    public function testGetNameFromAnnotation()
    {
        $namer = new PropertyNamer(new SnakePropertyNamingStrategy());
        $reflectionProperty = new ReflectionProperty(AnnotatedMock::class, 'fooBar');
        $factory = new AnnotationCollectionFactory(new AnnotationReader(), new VoidCache());
        $annotations = $factory->createPropertyAnnotations($reflectionProperty->getDeclaringClass()->getName(), $reflectionProperty->getName());

        self::assertSame('foobar', $namer->serializedName($reflectionProperty, $annotations));
    }

    public function testGetNameUsingStrategy()
    {
        $namer = new PropertyNamer(new SnakePropertyNamingStrategy());
        $reflectionProperty = new ReflectionProperty(AnnotatedMock::class, 'fooBarBaz');
        $factory = new AnnotationCollectionFactory(new AnnotationReader(), new VoidCache());
        $annotations = $factory->createPropertyAnnotations($reflectionProperty->getDeclaringClass()->getName(), $reflectionProperty->getName());

        self::assertSame('foo_bar_baz', $namer->serializedName($reflectionProperty, $annotations));
    }
}
