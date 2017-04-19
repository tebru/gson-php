<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter\Factory;


use PHPUnit_Framework_TestCase;
use Tebru\Gson\Element\JsonArray;
use Tebru\Gson\Element\JsonElement;
use Tebru\Gson\Element\JsonNull;
use Tebru\Gson\Element\JsonObject;
use Tebru\Gson\Element\JsonPrimitive;
use Tebru\Gson\Internal\TypeAdapter\JsonElementTypeAdapter;
use Tebru\Gson\Internal\TypeAdapter\Factory\JsonElementTypeAdapterFactory;

use Tebru\Gson\Test\Mock\ChildClass;
use Tebru\Gson\Test\MockProvider;
use Tebru\PhpType\TypeToken;

/**
 * Class JsonElementTypeAdapterFactoryTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\TypeAdapter\Factory\JsonElementTypeAdapterFactory
 */
class JsonElementTypeAdapterFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getValidSupports
     */
    public function testValidSupports($class)
    {
        $factory = new JsonElementTypeAdapterFactory();

        self::assertTrue($factory->supports(new TypeToken($class)));
    }

    public function testInvalidSupport()
    {
        $factory = new JsonElementTypeAdapterFactory();

        self::assertFalse($factory->supports(new TypeToken(ChildClass::class)));
    }

    public function testNonClassSupports()
    {
        $factory = new JsonElementTypeAdapterFactory();

        self::assertFalse($factory->supports(new TypeToken('string')));
    }

    public function testCreate()
    {
        $factory = new JsonElementTypeAdapterFactory();
        $adapter = $factory->create(new TypeToken(JsonElement::class), MockProvider::typeAdapterProvider());

        self::assertInstanceOf(JsonElementTypeAdapter::class, $adapter);
    }

    public function getValidSupports()
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
