<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\TypeAdapter;

use PHPUnit\Framework\TestCase;
use Tebru\Gson\Internal\DefaultReaderContext;
use Tebru\Gson\Internal\Excluder;
use Tebru\Gson\Internal\ObjectConstructor\CreateFromInstance;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\TypeAdapter\ReflectionTypeAdapter;
use Tebru\Gson\Test\Mock\AddressMock;
use Tebru\Gson\Test\Mock\ExclusionStrategies\UserEmailExclusionStrategy;
use Tebru\Gson\Test\Mock\ExclusionStrategies\UserMockExclusionStrategy;
use Tebru\Gson\Test\Mock\UserMock;
use Tebru\Gson\Test\Mock\UserMockExcluded;
use Tebru\Gson\Test\Mock\UserMockVirtual;
use Tebru\Gson\Test\MockProvider;
use Tebru\PhpType\TypeToken;

/**
 * Class ReflectionTypeAdapterTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\ObjectConstructorAwareTrait
 * @covers \Tebru\Gson\TypeAdapter\ReflectionTypeAdapter
 * @covers \Tebru\Gson\TypeAdapter
 */
class ReflectionTypeAdapterTest extends TestCase
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

    public function testSkipDeserialize(): void
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken(UserMockExcluded::class));
        $result = $adapter->readFromJson('{"foo": "bar"}');

        self::assertNull($result);
    }

    public function testDeserializeNull(): void
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken(UserMock::class));
        $result = $adapter->readFromJson('null');

        self::assertNull($result);
    }

    public function testDeserialize(): void
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

    public function testDeserializeOverrideObjectConstructor(): void
    {
        /** @var ReflectionTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken(UserMock::class));

        $address = new AddressMock();
        $address->setCity('My City');
        $address->setStreet('Foo');

        $userMock = new UserMock();
        $userMock->setAddress($address);

        $adapter->setObjectConstructor(new CreateFromInstance($userMock));
        $context = new DefaultReaderContext();
        $context->setUsesExistingObject(true);

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
        ', $context);
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

    public function testDeserializeOverrideObjectConstructorWithoutInner(): void
    {
        /** @var ReflectionTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken(UserMock::class));

        $userMock = new UserMock();

        $adapter->setObjectConstructor(new CreateFromInstance($userMock));
        $context = new DefaultReaderContext();
        $context->setUsesExistingObject(true);

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
        ', $context);
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

    public function testDeserializeExcludeClass(): void
    {
        $this->excluder->addExclusionStrategy(new UserMockExclusionStrategy());

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

    public function testDeserializeExcludeEmail(): void
    {
        $this->excluder->addExclusionStrategy(new UserEmailExclusionStrategy());

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

        self::assertInstanceOf(UserMock::class, $user);
        self::assertNull($user->getEmail());
    }

    public function testDeserializeVirtualProperty(): void
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

    public function testExcludeSerialize(): void
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken(UserMockExcluded::class));

        self::assertSame('null', $adapter->writeToJson('{"foo": "bar"}', false));
    }

    public function testSerializeNull(): void
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken(UserMock::class));

        self::assertSame('null', $adapter->writeToJson(null, false));
    }

    public function testSerialize(): void
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

    public function testSerializeExcludeClass(): void
    {
        $this->excluder->addExclusionStrategy(new UserMockExclusionStrategy());

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

    public function testSerializeExcludeProperty(): void
    {
        $this->excluder->addExclusionStrategy(new UserEmailExclusionStrategy());

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

    public function testSerializeVirtual(): void
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
