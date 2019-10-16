<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\TypeAdapter\Factory;


use PHPUnit\Framework\TestCase;
use Tebru\Gson\Element\JsonArray;
use Tebru\Gson\Element\JsonElement;
use Tebru\Gson\Element\JsonNull;
use Tebru\Gson\Element\JsonObject;
use Tebru\Gson\Element\JsonPrimitive;
use Tebru\Gson\TypeAdapter\Factory\JsonElementTypeAdapterFactory;
use Tebru\Gson\TypeAdapter\JsonElementTypeAdapter;
use Tebru\Gson\Test\Mock\ChildClass;
use Tebru\Gson\Test\MockProvider;
use Tebru\PhpType\TypeToken;

/**
 * Class JsonElementTypeAdapterFactoryTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\TypeAdapter\Factory\JsonElementTypeAdapterFactory
 */
class JsonElementTypeAdapterFactoryTest extends TestCase
{
    public function testInvalidSupport(): void
    {
        $factory = new JsonElementTypeAdapterFactory();
        $adapter = $factory->create(new TypeToken(ChildClass::class), MockProvider::typeAdapterProvider());

        self::assertNull($adapter);
    }

    public function testNonClassSupports(): void
    {
        $factory = new JsonElementTypeAdapterFactory();
        $adapter = $factory->create(new TypeToken('string'), MockProvider::typeAdapterProvider());

        self::assertNull($adapter);
    }

    public function testCreate(): void
    {
        $factory = new JsonElementTypeAdapterFactory();
        $adapter = $factory->create(new TypeToken(JsonElement::class), MockProvider::typeAdapterProvider());

        self::assertInstanceOf(JsonElementTypeAdapter::class, $adapter);
    }

    public function getValidSupports(): array
    {
        return [
            [JsonElement::class],
            [JsonPrimitive::class],
            [JsonNull::class],
            [JsonObject::class],
            [JsonArray::class],
        ];
    }
}
