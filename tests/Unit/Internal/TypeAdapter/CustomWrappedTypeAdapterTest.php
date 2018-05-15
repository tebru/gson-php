<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter;

use PHPUnit_Framework_TestCase;
use Tebru\Gson\Internal\TypeAdapter\Factory\CustomWrappedTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\CustomWrappedTypeAdapter;
use Tebru\Gson\Test\Mock\AddressMock;
use Tebru\Gson\Test\Mock\MockDeserializer;
use Tebru\Gson\Test\Mock\MockSerializer;
use Tebru\Gson\Test\Mock\UserMock;
use Tebru\Gson\Test\MockProvider;
use Tebru\PhpType\TypeToken;

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
        $typeAdapterProvider = MockProvider::typeAdapterProvider(
            MockProvider::excluder(),
            [new CustomWrappedTypeAdapterFactory(new TypeToken(UserMock::class), false, null, new MockDeserializer())]
        );

        /** @var CustomWrappedTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new TypeToken(UserMock::class));

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
        $typeAdapterProvider = MockProvider::typeAdapterProvider(
            MockProvider::excluder(),
            [new CustomWrappedTypeAdapterFactory(new TypeToken(UserMock::class), false)]
        );

        $adapter = $typeAdapterProvider->getAdapter(new TypeToken(UserMock::class));

        /** @var UserMock $user */
        $user = $adapter->readFromJson($this->json());

        self::assertSame(1, $user->getId());
        self::assertSame('test@example.com', $user->getEmail());
        self::assertSame('John Doe', $user->getName());
        self::assertSame('(123) 456-7890', $user->getPhone());
        self::assertTrue($user->isEnabled());
        self::assertNull($user->getPassword());
    }

    public function testUsesSerializerNull()
    {
        $typeAdapterProvider = MockProvider::typeAdapterProvider(
            MockProvider::excluder(),
            [new CustomWrappedTypeAdapterFactory(new TypeToken(UserMock::class), false, new MockSerializer())]
        );

        /** @var CustomWrappedTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new TypeToken(UserMock::class));

        self::assertSame('null', $adapter->writeToJson(null, false));
    }

    public function testUsesSerializer()
    {
        $typeAdapterProvider = MockProvider::typeAdapterProvider(
            MockProvider::excluder(),
            [new CustomWrappedTypeAdapterFactory(new TypeToken(UserMock::class), false, new MockSerializer())]
        );

        /** @var CustomWrappedTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new TypeToken(UserMock::class));

        $expected = json_decode($this->json(), true);
        unset($expected['password']);

        self::assertJsonStringEqualsJsonString(json_encode($expected), $adapter->writeToJson($this->user(), false));
    }

    public function testDelegatesSerialization()
    {
        $typeAdapterProvider = MockProvider::typeAdapterProvider(
            MockProvider::excluder(),
            [new CustomWrappedTypeAdapterFactory(new TypeToken(UserMock::class), false)]
        );

        /** @var CustomWrappedTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new TypeToken(UserMock::class));

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
            "phone": "(123) 456-7890"
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
