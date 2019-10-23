<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\TypeAdapter;

use Tebru\Gson\Context\ReaderContext;
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
class ReflectionTypeAdapterTest extends TypeAdapterTestCase
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
        parent::setUp();

        $this->excluder = MockProvider::excluder();
        $this->typeAdapterProvider = MockProvider::typeAdapterProvider($this->excluder);
    }

    public function testSkipDeserialize(): void
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken(UserMockExcluded::class));
        $result = $adapter->read(json_decode('{"foo": "bar"}', true), $this->readerContext);

        self::assertNull($result);
    }

    public function testDeserializeNull(): void
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken(UserMock::class));
        $result = $adapter->read(json_decode('null', true), $this->readerContext);

        self::assertNull($result);
    }

    public function testDeserialize(): void
    {
        /** @var ReflectionTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken(UserMock::class));

        /** @var UserMock $user */
        $user = $adapter->read(json_decode('
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
        ', true), $this->readerContext);
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

    public function testDeserializeWithoutScalarTypeAdapters(): void
    {
        /** @var ReflectionTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken(UserMock::class));
        $this->readerContext->setEnableScalarAdapters(false);

        /** @var UserMock $user */
        $user = $adapter->read(json_decode('
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
        ', true), $this->readerContext);
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
        $this->readerContext->setUsesExistingObject(true);

        /** @var UserMock $user */
        $user = $adapter->read(json_decode('
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
        ', true), $this->readerContext);
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
        $this->readerContext->setUsesExistingObject(true);

        /** @var UserMock $user */
        $user = $adapter->read(json_decode('
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
        ', true), $this->readerContext);
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
        $user = $adapter->read(json_decode('
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
        ', true), $this->readerContext);

        self::assertNull($user);
    }

    public function testDeserializeExcludeEmail(): void
    {
        $this->excluder->addExclusionStrategy(new UserEmailExclusionStrategy());

        /** @var ReflectionTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken(UserMock::class));

        /** @var UserMock $user */
        $user = $adapter->read(json_decode('
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
        ', true), $this->readerContext);

        self::assertInstanceOf(UserMock::class, $user);
        self::assertNull($user->getEmail());
    }

    public function testDeserializeVirtualProperty(): void
    {
        /** @var ReflectionTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken(UserMockVirtual::class));

        /** @var UserMock $user */
        $user = $adapter->read(json_decode('
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
        ', true), $this->readerContext);
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

        self::assertNull($adapter->write('{"foo": "bar"}', $this->writerContext));
    }

    public function testSerializeNull(): void
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken(UserMock::class));

        self::assertNull($adapter->write(null, $this->writerContext));
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

        self::assertJsonStringEqualsJsonString($expectedJson, json_encode($adapter->write($user, $this->writerContext)));
    }

    public function testSerializeWithoutScalarTypeAdapters(): void
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken(UserMock::class));
        $this->writerContext->setEnableScalarAdapters(false);

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

        self::assertJsonStringEqualsJsonString($expectedJson, json_encode($adapter->write($user, $this->writerContext)));
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

        self::assertNull($adapter->write($user, $this->writerContext));
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

        self::assertJsonStringEqualsJsonString($expectedJson, json_encode($adapter->write($user, $this->writerContext)));
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

        self::assertJsonStringEqualsJsonString($expectedJson, json_encode($adapter->write($user, $this->writerContext)));
    }
}
