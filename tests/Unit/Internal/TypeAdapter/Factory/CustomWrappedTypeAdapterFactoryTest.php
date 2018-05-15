<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter\Factory;


use PHPUnit_Framework_TestCase;
use Tebru\Gson\Internal\TypeAdapter\CustomWrappedTypeAdapter;
use Tebru\Gson\Internal\TypeAdapter\Factory\CustomWrappedTypeAdapterFactory;
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
 * @covers \Tebru\Gson\Internal\TypeAdapter\Factory\CustomWrappedTypeAdapterFactory
 */
class CustomWrappedTypeAdapterFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testSupportsObject()
    {
        $factory = new CustomWrappedTypeAdapterFactory(new TypeToken(UserMock::class), false, null, new MockDeserializer());

        self::assertTrue($factory->supports(new TypeToken(UserMock::class)));
    }

    public function testSupportsParent()
    {
        $factory = new CustomWrappedTypeAdapterFactory(new TypeToken(ChildClassParent::class), false, null, new MockDeserializer());

        self::assertTrue($factory->supports(new TypeToken(ChildClass::class)));
    }

    public function testIgnoresParentStrict()
    {
        $factory = new CustomWrappedTypeAdapterFactory(new TypeToken(ChildClassParent::class), true, null, new MockDeserializer());

        self::assertFalse($factory->supports(new TypeToken(ChildClass::class)));
    }

    public function testSupportsObjectFalse()
    {
        $factory = new CustomWrappedTypeAdapterFactory(new TypeToken(UserMock::class), false, null, new MockDeserializer());

        self::assertFalse($factory->supports(new TypeToken(ChildClass::class)));
    }

    public function testSupportsMismatchType()
    {
        $factory = new CustomWrappedTypeAdapterFactory(new TypeToken(UserMock::class), false, null, new MockDeserializer());

        self::assertFalse($factory->supports(new TypeToken('int')));
    }

    public function testCreate()
    {
        $factory = new CustomWrappedTypeAdapterFactory(new TypeToken(UserMock::class), false, new MockSerializer(), new MockDeserializer());
        $adapter = $factory->create(new TypeToken(UserMock::class), MockProvider::typeAdapterProvider());

        self::assertInstanceOf(CustomWrappedTypeAdapter::class, $adapter);
        self::assertAttributeInstanceOf(JsonSerializer::class, 'serializer', $adapter);
        self::assertAttributeInstanceOf(JsonDeserializer::class, 'deserializer', $adapter);
    }
}
