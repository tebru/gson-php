<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
namespace Tebru\Gson\Test\Unit\TypeAdapter;

use Tebru\Gson\Context\ReaderContext;
use Tebru\Gson\TypeAdapter\CustomWrappedTypeAdapter;
use Tebru\Gson\TypeAdapter\Factory\CustomWrappedTypeAdapterFactory;
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
 * @covers \Tebru\Gson\TypeAdapter\CustomWrappedTypeAdapter
 * @covers \Tebru\Gson\TypeAdapter
 */
class CustomWrappedTypeAdapterTest extends TypeAdapterTestCase
{
    public function testUsesDeserializer(): void
    {
        $typeAdapterProvider = MockProvider::typeAdapterProvider(
            MockProvider::excluder(),
            [new CustomWrappedTypeAdapterFactory(new TypeToken(UserMock::class), false, null, new MockDeserializer())]
        );

        /** @var CustomWrappedTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new TypeToken(UserMock::class));

        /** @var UserMock $user */
        $user = $adapter->read(json_decode($this->json(), true), new ReaderContext());

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

    public function testDeserializeNull(): void
    {
        $typeAdapterProvider = MockProvider::typeAdapterProvider(
            MockProvider::excluder(),
            [new CustomWrappedTypeAdapterFactory(new TypeToken(UserMock::class), false, null, new MockDeserializer())]
        );

        /** @var CustomWrappedTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new TypeToken(UserMock::class));
        $user = $adapter->read(null, new ReaderContext());

        self::assertNull($user);
    }

    public function testDelegatesDeserializer(): void
    {
        $typeAdapterProvider = MockProvider::typeAdapterProvider(
            MockProvider::excluder(),
            [new CustomWrappedTypeAdapterFactory(new TypeToken(UserMock::class), false)]
        );

        $adapter = $typeAdapterProvider->getAdapter(new TypeToken(UserMock::class));

        /** @var UserMock $user */
        $user = $adapter->read(json_decode($this->json(), true), new ReaderContext());

        self::assertSame(1, $user->getId());
        self::assertSame('test@example.com', $user->getEmail());
        self::assertSame('John Doe', $user->getName());
        self::assertSame('(123) 456-7890', $user->getPhone());
        self::assertTrue($user->isEnabled());
        self::assertNull($user->getPassword());
    }

    public function testUsesSerializerNull(): void
    {
        $typeAdapterProvider = MockProvider::typeAdapterProvider(
            MockProvider::excluder(),
            [new CustomWrappedTypeAdapterFactory(new TypeToken(UserMock::class), false, new MockSerializer())]
        );

        /** @var CustomWrappedTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new TypeToken(UserMock::class));

        self::assertNull($adapter->write(null, $this->writerContext));
    }

    public function testUsesSerializer(): void
    {
        $typeAdapterProvider = MockProvider::typeAdapterProvider(
            MockProvider::excluder(),
            [new CustomWrappedTypeAdapterFactory(new TypeToken(UserMock::class), false, new MockSerializer())]
        );

        /** @var CustomWrappedTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new TypeToken(UserMock::class));

        $expected = json_decode($this->json(), true);
        unset($expected['password']);

        self::assertJsonStringEqualsJsonString(json_encode($expected), json_encode($adapter->write($this->user(), $this->writerContext)));
    }

    public function testDelegatesSerialization(): void
    {
        $typeAdapterProvider = MockProvider::typeAdapterProvider(
            MockProvider::excluder(),
            [new CustomWrappedTypeAdapterFactory(new TypeToken(UserMock::class), false)]
        );

        /** @var CustomWrappedTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new TypeToken(UserMock::class));

        $json = $adapter->write($this->user(), $this->writerContext);

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

        self::assertJsonStringEqualsJsonString($expectedJson, json_encode($json));
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
