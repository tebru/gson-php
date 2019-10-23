<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
namespace Tebru\Gson\Test\Unit\TypeAdapter\Factory;


use PHPUnit\Framework\TestCase;
use Tebru\Gson\TypeAdapter\CustomWrappedTypeAdapter;
use Tebru\Gson\TypeAdapter\Factory\CustomWrappedTypeAdapterFactory;
use Tebru\Gson\JsonDeserializer;
use Tebru\Gson\JsonSerializer;
use Tebru\Gson\Test\Mock\ChildClass;
use Tebru\Gson\Test\Mock\ChildClassParent;
use Tebru\Gson\Test\Mock\MockDeserializer;
use Tebru\Gson\Test\Mock\MockSerializer;
use Tebru\Gson\Test\Mock\UserMock;
use Tebru\Gson\Test\MockProvider;
use Tebru\PhpType\TypeToken;

/**
 * Class CustomWrappedTypeAdapterFactoryTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\TypeAdapter\Factory\CustomWrappedTypeAdapterFactory
 */
class CustomWrappedTypeAdapterFactoryTest extends TestCase
{
    public function testIgnoresParentStrict(): void
    {
        $factory = new CustomWrappedTypeAdapterFactory(new TypeToken(ChildClassParent::class), true, null, new MockDeserializer());
        $adapter = $factory->create(new TypeToken(ChildClass::class), MockProvider::typeAdapterProvider());

        self::assertNull($adapter);
    }

    public function testSupportsObjectFalse(): void
    {
        $factory = new CustomWrappedTypeAdapterFactory(new TypeToken(UserMock::class), false, null, new MockDeserializer());
        $adapter = $factory->create(new TypeToken(ChildClass::class), MockProvider::typeAdapterProvider());

        self::assertNull($adapter);
    }

    public function testSupportsMismatchType(): void
    {
        $factory = new CustomWrappedTypeAdapterFactory(new TypeToken(UserMock::class), false, null, new MockDeserializer());
        $adapter = $factory->create(new TypeToken('int'), MockProvider::typeAdapterProvider());

        self::assertNull($adapter);
    }

    public function testSupportsObject(): void
    {
        $factory = new CustomWrappedTypeAdapterFactory(new TypeToken(UserMock::class), false, null, new MockDeserializer());
        $adapter = $factory->create(new TypeToken(UserMock::class), MockProvider::typeAdapterProvider());

        self::assertInstanceOf(CustomWrappedTypeAdapter::class, $adapter);
    }

    public function testSupportsParent(): void
    {
        $factory = new CustomWrappedTypeAdapterFactory(new TypeToken(ChildClassParent::class), false, null, new MockDeserializer());
        $adapter = $factory->create(new TypeToken(ChildClass::class), MockProvider::typeAdapterProvider());

        self::assertInstanceOf(CustomWrappedTypeAdapter::class, $adapter);
    }

    public function testCreate(): void
    {
        $factory = new CustomWrappedTypeAdapterFactory(new TypeToken(UserMock::class), false, new MockSerializer(), new MockDeserializer());
        $adapter = $factory->create(new TypeToken(UserMock::class), MockProvider::typeAdapterProvider());

        self::assertInstanceOf(CustomWrappedTypeAdapter::class, $adapter);
        self::assertAttributeInstanceOf(JsonSerializer::class, 'serializer', $adapter);
        self::assertAttributeInstanceOf(JsonDeserializer::class, 'deserializer', $adapter);
    }
}
