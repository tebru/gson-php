<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
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
use Tebru\Gson\Internal\PhpType;
use Tebru\Gson\Internal\PhpTypeFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\BooleanTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\ExcluderTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\FloatTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\IntegerTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\NullTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\ReflectionTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\StringTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\ReflectionTypeAdapter;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\Test\Mock\UserMock;

/**
 * Class ReflectionTypeAdapterTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\TypeAdapter\ReflectionTypeAdapter
 */
class ReflectionTypeAdapterTest extends PHPUnit_Framework_TestCase
{
    public function testNull()
    {
        $annotationCollectionFactory = new AnnotationCollectionFactory(new AnnotationReader());
        $excluder = new Excluder($annotationCollectionFactory);
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
        $typeAdapterProvider = new TypeAdapterProvider([
            new ReflectionTypeAdapterFactory(new ConstructorConstructor(), $propertyCollectionFactory)
        ]);

        $adapter = $typeAdapterProvider->getAdapter(new PhpType(UserMock::class));

        $result = $adapter->readFromJson('null');

        self::assertNull($result);
    }

    public function testDeserialize()
    {
        $annotationCollectionFactory = new AnnotationCollectionFactory(new AnnotationReader());
        $excluder = new Excluder($annotationCollectionFactory);
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
        $typeAdapterProvider = new TypeAdapterProvider([
            new ExcluderTypeAdapterFactory($excluder),
            new StringTypeAdapterFactory(),
            new IntegerTypeAdapterFactory(),
            new FloatTypeAdapterFactory(),
            new BooleanTypeAdapterFactory(),
            new NullTypeAdapterFactory(),
            new ReflectionTypeAdapterFactory(new ConstructorConstructor(), $propertyCollectionFactory)
        ]);

        /** @var ReflectionTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new PhpType(UserMock::class));

        /** @var UserMock $result */
        $result = $adapter->readFromJson('
            {
                "id": 1,
                "name": "Test User",
                "email": "test@example.com",
                "password": "password1",
                "address": {
                    "street": "123 ABC St.",
                    "city": "My City",
                    "state": "MN",
                    "zip": 12345
                },
                "phone": "1234567890",
                "enabled": true
            }
        ');

        self::assertInstanceOf(UserMock::class, $result);
    }
}
