<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\VoidCache;
use PHPUnit_Framework_TestCase;
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
use Tebru\Gson\PhpType;
use Tebru\Gson\Internal\PhpTypeFactory;
use Tebru\Gson\Internal\TypeAdapter\ExcluderTypeAdapter;
use Tebru\Gson\Internal\TypeAdapter\Factory\ExcluderTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\ReflectionTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\Test\Mock\ExcluderVersionMock;
use Tebru\Gson\Test\Mock\ExclusionStrategies\ExcludeClassMockExclusionStrategy;
use Tebru\Gson\Test\Mock\ExclusionStrategies\FooExclusionStrategy;
use Tebru\Gson\Test\Mock\Foo;
use Tebru\Gson\Test\Mock\TypeAdapter\FooTypeAdapterFactory;

/**
 * Class ExcluderTypeAdapterTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\TypeAdapter\ExcluderTypeAdapter
 * @covers \Tebru\Gson\TypeAdapter
 */
class ExcluderTypeAdapterTest extends PHPUnit_Framework_TestCase
{
    public function testDeserializeSkips()
    {
        $excluder = new Excluder(new AnnotationCollectionFactory(new AnnotationReader(), new VoidCache()));
        $excluder->addExclusionStrategy(new FooExclusionStrategy(), false, true);

        $typeAdapterProvider = new TypeAdapterProvider(
            [
                new ExcluderTypeAdapterFactory($excluder),
            ],
            new ArrayCache()
        );
        $adapter = $typeAdapterProvider->getAdapter(new PhpType('Foo'));

        self::assertNull($adapter->readFromJson('{}'));
    }

    public function testDeserializeDelegates()
    {
        $excluder = new Excluder(new AnnotationCollectionFactory(new AnnotationReader(), new VoidCache()));
        $excluder->addExclusionStrategy(new FooExclusionStrategy(), true, false);

        $typeAdapterProvider = new TypeAdapterProvider(
            [
                new ExcluderTypeAdapterFactory($excluder),
                new FooTypeAdapterFactory(),
            ],
            new ArrayCache()
        );

        /** @var ExcluderTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new PhpType('Foo'));

        self::assertEquals(new Foo(), $adapter->readFromJson('{}'));
    }

    public function testSerializeSkips()
    {
        $excluder = new Excluder(new AnnotationCollectionFactory(new AnnotationReader(), new VoidCache()));
        $excluder->addExclusionStrategy(new ExcludeClassMockExclusionStrategy(), true, false);

        $typeAdapterProvider = new TypeAdapterProvider(
            [
                new ExcluderTypeAdapterFactory($excluder),
            ],
            new ArrayCache()
        );
        $adapter = $typeAdapterProvider->getAdapter(new PhpType(ExcluderVersionMock::class));

        self::assertSame('null', $adapter->writeToJson(new ExcluderVersionMock(), false));
    }

    public function testSerializeDelegates()
    {
        $annotationCollectionFactory = new AnnotationCollectionFactory(new AnnotationReader(), new VoidCache());
        $excluder = new Excluder(new AnnotationCollectionFactory(new AnnotationReader(), new VoidCache()));
        $excluder->addExclusionStrategy(new ExcludeClassMockExclusionStrategy(), false, true);
        $propertyCollectionFactory = new PropertyCollectionFactory(
            new ReflectionPropertySetFactory(),
            $annotationCollectionFactory,
            new PropertyNamer(new SnakePropertyNamingStrategy()),
            new AccessorMethodProvider(new UpperCaseMethodNamingStrategy()),
            new AccessorStrategyFactory(),
            new PhpTypeFactory(),
            $excluder,
            new ArrayCache()
        );

        $typeAdapterProvider = new TypeAdapterProvider(
            [
                new ExcluderTypeAdapterFactory($excluder),
                new ReflectionTypeAdapterFactory(new ConstructorConstructor(), $propertyCollectionFactory, $excluder),
            ],
            new ArrayCache()
        );
        $adapter = $typeAdapterProvider->getAdapter(new PhpType(ExcluderVersionMock::class));

        self::assertSame('{}', $adapter->writeToJson(new ExcluderVersionMock(), false));
    }
}
