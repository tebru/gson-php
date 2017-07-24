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
use Tebru\AnnotationReader\AnnotationReaderAdapter;
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
        $annotationReader = new AnnotationReaderAdapter(new AnnotationReader(), new VoidCache());
        $annotations = $annotationReader->readProperty(
            $reflectionProperty->getName(),
            $reflectionProperty->getDeclaringClass()->getName(),
            false,
            true
        );

        self::assertSame('foobar', $namer->serializedName($reflectionProperty->getName(), $annotations));
    }

    public function testGetNameUsingStrategy()
    {
        $namer = new PropertyNamer(new SnakePropertyNamingStrategy());
        $reflectionProperty = new ReflectionProperty(AnnotatedMock::class, 'fooBarBaz');
        $annotationReader = new AnnotationReaderAdapter(new AnnotationReader(), new VoidCache());
        $annotations = $annotationReader->readProperty(
            $reflectionProperty->getName(),
            $reflectionProperty->getDeclaringClass()->getName(),
            false,
            true
        );

        self::assertSame('foo_bar_baz', $namer->serializedName($reflectionProperty->getName(), $annotations));
    }
}
