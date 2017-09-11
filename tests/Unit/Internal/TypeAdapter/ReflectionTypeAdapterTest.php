<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter;

use PHPUnit_Framework_TestCase;
use Tebru\Gson\Internal\Excluder;
use Tebru\Gson\Internal\ObjectConstructor\CreateFromInstance;
use Tebru\Gson\Internal\TypeAdapter\ReflectionTypeAdapter;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\Test\Mock\AddressMock;
use Tebru\Gson\Test\Mock\ExclusionStrategies\UserMockExclusionStrategy;
use Tebru\Gson\Test\Mock\UserMock;
use Tebru\Gson\Test\Mock\UserMockVirtual;
use Tebru\Gson\Test\MockProvider;
use Tebru\PhpType\TypeToken;

/**
 * Class ReflectionTypeAdapterTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\ObjectConstructorAwareTrait
 * @covers \Tebru\Gson\Internal\TypeAdapter\ReflectionTypeAdapter
 * @covers \Tebru\Gson\TypeAdapter
 */
class ReflectionTypeAdapterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Excluder
     */
    private $excluder;

    /**
     * @var TypeAdapterProvider
     */
    private $typeAdapterProvider;

    public function setUp()
    {
        $this->excluder = MockProvider::excluder();
        $this->typeAdapterProvider = MockProvider::typeAdapterProvider($this->excluder);
    }

    public function testDeserializeNull()
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken(UserMock::class));
        $result = $adapter->readFromJson('null');

        self::assertNull($result);
    }

    public function testDeserialize()
    {
        /** @var ReflectionTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken(UserMock::class));

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

    public function testDeserializeOverrideObjectConstructor()
    {
        /** @var ReflectionTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken(UserMock::class));

        $address = new AddressMock();
        $address->setCity('My City');
        $address->setStreet('Foo');

        $userMock = new UserMock();
        $userMock->setAddress($address);

        $adapter->setObjectConstructor(new CreateFromInstance($userMock));

        /** @var UserMock $user */
        $user = $adapter->readFromJson('
            {
                "id": 1,
                "name": "Test User",
                "email": "test@example.com",
                "password": "password1",
                "address": {
                    "street": "123 ABC St.",
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
        $this->excluder->addExclusionStrategy(new UserMockExclusionStrategy(), false, true);

        /** @var ReflectionTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken(UserMock::class));

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

    public function testDeserializeVirtualProperty()
    {
        /** @var ReflectionTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken(UserMockVirtual::class));

        /** @var UserMock $user */
        $user = $adapter->readFromJson('
            {
                "data": {
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
            }
        ');
        $address = $user->getAddress();

        self::assertInstanceOf(UserMockVirtual::class, $user);
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
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken(UserMock::class));

        self::assertSame('null', $adapter->writeToJson(null, false));
    }

    public function testSerialize()
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken(UserMock::class));

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
            "phone": "(123) 456-7890"
        }';

        self::assertJsonStringEqualsJsonString($expectedJson, $adapter->writeToJson($user, false));
    }

    public function testSerializeExcludeClass()
    {
        $this->excluder->addExclusionStrategy(new UserMockExclusionStrategy(), true, false);

        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken(UserMock::class));

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

    public function testSerializeVirtual()
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken(UserMockVirtual::class));

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
            "data": {
                "id": 1,
                "email": "test@example.com",
                "name": "John Doe",
                "address": {
                    "street": "123 ABC St",
                    "city": "Foo",
                    "state": "MN",
                    "zip": 12345
                },
                "phone": "(123) 456-7890"
            }
        }';

        self::assertJsonStringEqualsJsonString($expectedJson, $adapter->writeToJson($user, false));
    }
}
