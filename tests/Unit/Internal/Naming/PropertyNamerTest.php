<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\Naming;

use Doctrine\Common\Annotations\AnnotationReader;
use PHPUnit_Framework_TestCase;
use ReflectionMethod;
use ReflectionProperty;
use Symfony\Component\Cache\Simple\NullCache;
use Tebru\AnnotationReader\AnnotationReaderAdapter;
use Tebru\Gson\Internal\Naming\PropertyNamer;
use Tebru\Gson\Internal\Naming\DefaultPropertyNamingStrategy;
use Tebru\Gson\PropertyNamingPolicy;
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
        $namer = new PropertyNamer(new DefaultPropertyNamingStrategy(PropertyNamingPolicy::LOWER_CASE_WITH_UNDERSCORES));
        $reflectionProperty = new ReflectionProperty(AnnotatedMock::class, 'fooBar');
        $annotationReader = new AnnotationReaderAdapter(new AnnotationReader(), new NullCache());
        $annotations = $annotationReader->readProperty(
            $reflectionProperty->getName(),
            $reflectionProperty->getDeclaringClass()->getName(),
            false,
            true
        );

        self::assertSame('foobar', $namer->serializedName($reflectionProperty->getName(), $annotations));
    }

    public function testGetNameFromVirtualAnnotationOnMethod()
    {
        $namer = new PropertyNamer(new DefaultPropertyNamingStrategy(PropertyNamingPolicy::LOWER_CASE_WITH_UNDERSCORES));
        $reflectionMethod = new ReflectionMethod(AnnotatedMock::class, 'virtualFoo');
        $annotationReader = new AnnotationReaderAdapter(new AnnotationReader(), new NullCache());
        $annotations = $annotationReader->readMethod(
            $reflectionMethod->getName(),
            $reflectionMethod->getDeclaringClass()->getName(),
            false,
            true
        );

        self::assertSame('vfoo', $namer->serializedName($reflectionMethod->getName(), $annotations));
    }

    public function testGetNameFromVirtualAnnotationOnMethodUsesSerializedName()
    {
        $namer = new PropertyNamer(new DefaultPropertyNamingStrategy(PropertyNamingPolicy::LOWER_CASE_WITH_UNDERSCORES));
        $reflectionMethod = new ReflectionMethod(AnnotatedMock::class, 'virtualFooWithSerializedName');
        $annotationReader = new AnnotationReaderAdapter(new AnnotationReader(), new NullCache());
        $annotations = $annotationReader->readMethod(
            $reflectionMethod->getName(),
            $reflectionMethod->getDeclaringClass()->getName(),
            false,
            true
        );

        self::assertSame('vfooOverride', $namer->serializedName($reflectionMethod->getName(), $annotations));
    }

    public function testGetNameUsingStrategy()
    {
        $namer = new PropertyNamer(new DefaultPropertyNamingStrategy(PropertyNamingPolicy::LOWER_CASE_WITH_UNDERSCORES));
        $reflectionProperty = new ReflectionProperty(AnnotatedMock::class, 'fooBarBaz');
        $annotationReader = new AnnotationReaderAdapter(new AnnotationReader(), new NullCache());
        $annotations = $annotationReader->readProperty(
            $reflectionProperty->getName(),
            $reflectionProperty->getDeclaringClass()->getName(),
            false,
            true
        );

        self::assertSame('foo_bar_baz', $namer->serializedName($reflectionProperty->getName(), $annotations));
    }
}
