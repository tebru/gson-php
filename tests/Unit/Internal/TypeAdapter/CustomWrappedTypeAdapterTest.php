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
use Tebru\Gson\Internal\PhpType;
use Tebru\Gson\Internal\PhpTypeFactory;
use Tebru\Gson\Internal\TypeAdapter\CustomWrappedTypeAdapter;
use Tebru\Gson\Internal\TypeAdapter\Factory\BooleanTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\CustomWrappedTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\IntegerTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\ReflectionTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\StringTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\Test\Mock\AddressMock;
use Tebru\Gson\Test\Mock\MockDeserializer;
use Tebru\Gson\Test\Mock\MockSerializer;
use Tebru\Gson\Test\Mock\UserMock;

/**
 * Class CustomWrappedTypeAdapterTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\TypeAdapter\CustomWrappedTypeAdapter
 * @covers \Tebru\Gson\TypeAdapter
 */
class CustomWrappedTypeAdapterTest extends PHPUnit_Framework_TestCase
{
    public function testUsesDeserializer()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new CustomWrappedTypeAdapterFactory(new PhpType(UserMock::class), null, new MockDeserializer()),
        ]);

        /** @var CustomWrappedTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new PhpType(UserMock::class));

        /** @var UserMock $user */
        $user = $adapter->readFromJson($this->json());

        $address = $user->getAddress();

        self::assertSame(1, $user->getId());
        self::assertSame('test@example.com', $user->getEmail());
        self::assertSame('John Doe', $user->getName());
        self::assertSame('(123) 456-7890', $user->getPhone());
        self::assertTrue($user->isEnabled());
        self::assertNull($user->getPassword());
        self::assertSame('123 ABC St', $address->getStreet());
        self::assertSame('Foo', $address->getCity());
        self::assertSame('MN', $address->getState());
        self::assertSame(12345, $address->getZip());
    }

    public function testDelegatesDeserializer()
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
            new CustomWrappedTypeAdapterFactory(new PhpType(UserMock::class)),
            new ReflectionTypeAdapterFactory(new ConstructorConstructor(), $propertyCollectionFactory, $excluder),
        ]);

        $adapter = $typeAdapterProvider->getAdapter(new PhpType(UserMock::class));

        /** @var UserMock $user */
        $user = $adapter->readFromJson($this->json());

        self::assertSame(1, $user->getId());
        self::assertSame('test@example.com', $user->getEmail());
        self::assertSame('John Doe', $user->getName());
        self::assertSame('(123) 456-7890', $user->getPhone());
        self::assertTrue($user->isEnabled());
        self::assertNull($user->getPassword());
        self::assertNull($user->getAddress());
    }

    public function testUsesSerializerNull()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new CustomWrappedTypeAdapterFactory(new PhpType(UserMock::class), new MockSerializer()),
        ]);

        /** @var CustomWrappedTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new PhpType(UserMock::class));

        self::assertSame('null', $adapter->writeToJson(null, false));
    }

    public function testUsesSerializer()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new CustomWrappedTypeAdapterFactory(new PhpType(UserMock::class), new MockSerializer()),
        ]);

        /** @var CustomWrappedTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new PhpType(UserMock::class));

        $expected = json_decode($this->json(), true);
        unset($expected['password']);

        self::assertJsonStringEqualsJsonString(json_encode($expected), $adapter->writeToJson($this->user(), false));
    }

    public function testDelegatesSerialization()
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
            new CustomWrappedTypeAdapterFactory(new PhpType(UserMock::class)),
            new ReflectionTypeAdapterFactory(new ConstructorConstructor(), $propertyCollectionFactory, $excluder),
        ]);

        /** @var CustomWrappedTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new PhpType(UserMock::class));

        $json = $adapter->writeToJson($this->user(), false);

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

        self::assertJsonStringEqualsJsonString($expectedJson, $json);
    }

    private function json(): string
    {
        return '{
            "id": 1,
            "email": "test@example.com",
            "password": "password1",
            "name": "John Doe",
            "street": "123 ABC St",
            "city": "Foo",
            "state": "MN",
            "zip": 12345,
            "phone": "(123) 456-7890",
            "enabled": true
        }';
    }

    private function user(): UserMock
    {
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

        return $user;
    }
}
