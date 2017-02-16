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
use Tebru\Gson\Internal\TypeAdapter\Factory\BooleanTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\ExcluderTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\FloatTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\IntegerTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\NullTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\ReflectionTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\StringTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\ReflectionTypeAdapter;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\Test\Mock\AddressMock;
use Tebru\Gson\Test\Mock\UserMock;

/**
 * Class ReflectionTypeAdapterTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\TypeAdapter\ReflectionTypeAdapter
 * @covers \Tebru\Gson\TypeAdapter
 */
class ReflectionTypeAdapterTest extends PHPUnit_Framework_TestCase
{
    public function testDeserializeNull()
    {
        $annotationCollectionFactory = new AnnotationCollectionFactory(new AnnotationReader(), new VoidCache());
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
            new StringTypeAdapterFactory(),
            new IntegerTypeAdapterFactory(),
            new BooleanTypeAdapterFactory(),
            new ReflectionTypeAdapterFactory(new ConstructorConstructor(), $propertyCollectionFactory, $excluder)
        ]);

        $adapter = $typeAdapterProvider->getAdapter(new PhpType(UserMock::class));

        $result = $adapter->readFromJson('null');

        self::assertNull($result);
    }

    public function testDeserialize()
    {
        $annotationCollectionFactory = new AnnotationCollectionFactory(new AnnotationReader(), new VoidCache());
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
            new ReflectionTypeAdapterFactory(new ConstructorConstructor(), $propertyCollectionFactory, $excluder)
        ]);

        /** @var ReflectionTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new PhpType(UserMock::class));

        /** @var UserMock $user */
        $user = $adapter->readFromJson('
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
                "phone": null,
                "enabled": true
            }
        ');
        $address = $user->getAddress();

        self::assertInstanceOf(UserMock::class, $user);
        self::assertSame(1, $user->getId());
        self::assertSame('test@example.com', $user->getEmail());
        self::assertSame('Test User', $user->getName());
        self::assertNull($user->getPhone());
        self::assertTrue($user->isEnabled());
        self::assertNull($user->getPassword());
        self::assertSame('123 ABC St.', $address->getStreet());
        self::assertSame('My City', $address->getCity());
        self::assertSame('MN', $address->getState());
        self::assertSame(12345, $address->getZip());
    }

    public function testSerializeNull()
    {
        $annotationCollectionFactory = new AnnotationCollectionFactory(new AnnotationReader(), new VoidCache());
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
            new StringTypeAdapterFactory(),
            new IntegerTypeAdapterFactory(),
            new BooleanTypeAdapterFactory(),
            new ReflectionTypeAdapterFactory(new ConstructorConstructor(), $propertyCollectionFactory, $excluder)
        ]);

        $adapter = $typeAdapterProvider->getAdapter(new PhpType(UserMock::class));

        self::assertSame('null', $adapter->writeToJson(null, false));
    }

    public function testSerialize()
    {
        $annotationCollectionFactory = new AnnotationCollectionFactory(new AnnotationReader(), new VoidCache());
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
            new StringTypeAdapterFactory(),
            new IntegerTypeAdapterFactory(),
            new BooleanTypeAdapterFactory(),
            new ReflectionTypeAdapterFactory(new ConstructorConstructor(), $propertyCollectionFactory, $excluder)
        ]);

        $adapter = $typeAdapterProvider->getAdapter(new PhpType(UserMock::class));

        $user = new UserMock();
        $user->setId(1);
        $user->setEmail('test@example.com');
        $user->setPassword('password1');
        $user->setName('John Doe');
        $user->setPhone('(123) 456-7890');
        $user->setEnabled(true);

        $address = new AddressMock();
        $address->setStreet('123 ABC St');
        $address->setCity('Foo');
        $address->setState('MN');
        $address->setZip(12345);

        $user->setAddress($address);

        $expectedJson = '{
            "id": 1,
            "email": "test@example.com",
            "name": "John Doe",
            "address": {
                "street": "123 ABC St",
                "city": "Foo",
                "state": "MN",
                "zip": 12345
            },
            "phone": "(123) 456-7890",
            "enabled": true
        }';

        self::assertJsonStringEqualsJsonString($expectedJson, $adapter->writeToJson($user, false));
    }
}
