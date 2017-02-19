<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\VoidCache;
use PHPUnit_Framework_TestCase;
use Tebru\Gson\Internal\AccessorMethodProvider;
use Tebru\Gson\Internal\AccessorStrategyFactory;
use Tebru\Gson\Internal\ConstructorConstructor;
use Tebru\Gson\Internal\Data\AnnotationCollectionFactory;
use Tebru\Gson\Internal\Data\PropertyCollectionFactory;
use Tebru\Gson\Internal\Data\ReflectionPropertySetFactory;
use Tebru\Gson\Internal\Excluder;
use Tebru\Gson\Internal\MetadataFactory;
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
use Tebru\Gson\Test\Mock\ExclusionStrategies\UserMockExclusionStrategy;
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
    /**
     * @var TypeAdapterProvider
     */
    private $typeAdapterProvider;

    public function setUp()
    {
        $annotationCollectionFactory = $annotationCollectionFactory = new AnnotationCollectionFactory(new AnnotationReader(), new VoidCache());
        $metadataFactory = new MetadataFactory($annotationCollectionFactory);
        $excluder = new Excluder();
        $propertyCollectionFactory = new PropertyCollectionFactory(
            new ReflectionPropertySetFactory(),
            $annotationCollectionFactory,
            $metadataFactory,
            new PropertyNamer(new SnakePropertyNamingStrategy()),
            new AccessorMethodProvider(new UpperCaseMethodNamingStrategy()),
            new AccessorStrategyFactory(),
            new PhpTypeFactory(),
            $excluder,
            new VoidCache()
        );
        $this->typeAdapterProvider = $typeAdapterProvider = new TypeAdapterProvider(
            [
                new ExcluderTypeAdapterFactory($excluder, $metadataFactory),
                new StringTypeAdapterFactory(),
                new IntegerTypeAdapterFactory(),
                new FloatTypeAdapterFactory(),
                new BooleanTypeAdapterFactory(),
                new NullTypeAdapterFactory(),
                new ReflectionTypeAdapterFactory(
                    new ConstructorConstructor(),
                    $propertyCollectionFactory,
                    $metadataFactory
                )
            ],
            new VoidCache()
        );
    }

    public function testDeserializeNull()
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new PhpType(UserMock::class));
        $result = $adapter->readFromJson('null');

        self::assertNull($result);
    }

    public function testDeserialize()
    {
        /** @var ReflectionTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new PhpType(UserMock::class));

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

    public function testDeserializeExcludeClass()
    {
        Excluder::addExclusionStrategy(new UserMockExclusionStrategy(), false, true);

        /** @var ReflectionTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new PhpType(UserMock::class));

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

        self::assertNull($user);
    }

    public function testSerializeNull()
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new PhpType(UserMock::class));

        self::assertSame('null', $adapter->writeToJson(null, false));
    }

    public function testSerialize()
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new PhpType(UserMock::class));

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

    public function testSerializeExcludeClass()
    {
        Excluder::addExclusionStrategy(new UserMockExclusionStrategy(), true, false);

        $adapter = $this->typeAdapterProvider->getAdapter(new PhpType(UserMock::class));

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

        self::assertSame('null', $adapter->writeToJson($user, false));
    }
}
