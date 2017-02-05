<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter\Factory;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\VoidCache;
use PHPUnit_Framework_TestCase;
use Tebru\Collection\HashMap;
use Tebru\Gson\Internal\AccessorMethodProvider;
use Tebru\Gson\Internal\AccessorStrategyFactory;
use Tebru\Gson\Internal\ConstructorConstructor;
use Tebru\Gson\Internal\Data\AnnotationCollectionFactory;
use Tebru\Gson\Internal\Data\PropertyCollectionFactory;
use Tebru\Gson\Internal\Data\ReflectionPropertySetFactory;
use Tebru\Gson\Internal\Excluder;
use Tebru\Gson\Internal\Naming\PropertyNamer;
use Tebru\Gson\Internal\Naming\SnakePropertyNamingStrategy;
use Tebru\Gson\Internal\Naming\UpperCaseMethodNamingStrategy;
use Tebru\Gson\Internal\PhpType;
use Tebru\Gson\Internal\PhpTypeFactory;
use Tebru\Gson\Internal\TypeAdapter\ReflectionTypeAdapter;
use Tebru\Gson\Internal\TypeAdapter\Factory\ReflectionTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\Test\Mock\ChildClass;

/**
 * Class ReflectionTypeAdapterFactoryTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\TypeAdapter\Factory\ReflectionTypeAdapterFactory
 */
class ReflectionTypeAdapterFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testValidSupports()
    {
        self::assertTrue($this->factory()->supports(new PhpType(ChildClass::class)));
    }

    public function testNonClassSupports()
    {
        self::assertFalse($this->factory()->supports(new PhpType('string')));
    }

    public function testCreate()
    {
        $adapter = $this->factory()->create(new PhpType(ChildClass::class), new TypeAdapterProvider([]));

        self::assertInstanceOf(ReflectionTypeAdapter::class, $adapter);
    }

    private function factory(): ReflectionTypeAdapterFactory
    {
        $annotationCollectionFactory = new AnnotationCollectionFactory(new AnnotationReader());
        $propertyCollectionFactory = new PropertyCollectionFactory(
            new ReflectionPropertySetFactory(),
            $annotationCollectionFactory,
            new PropertyNamer(new SnakePropertyNamingStrategy()),
            new AccessorMethodProvider(new UpperCaseMethodNamingStrategy()),
            new AccessorStrategyFactory(),
            new PhpTypeFactory(),
            new Excluder($annotationCollectionFactory),
            new VoidCache()
        );

        return new ReflectionTypeAdapterFactory(new ConstructorConstructor(), $propertyCollectionFactory);
    }
}
