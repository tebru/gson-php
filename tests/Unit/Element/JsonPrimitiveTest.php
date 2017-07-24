<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
namespace Tebru\Gson\Test\Unit\Element;

use PHPUnit_Framework_TestCase;
use Tebru\Gson\Element\JsonNull;
use Tebru\Gson\Element\JsonPrimitive;

/**
 * Class JsonPrimitiveTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Element\JsonPrimitive
 */
class JsonPrimitiveTest extends PHPUnit_Framework_TestCase
{
    public function testNull()
    {
        self::assertInstanceOf(JsonNull::class, JsonPrimitive::create(null));
    }

    public function testString()
    {
        $primitive = JsonPrimitive::create('test');

        self::assertTrue($primitive->isString());
        self::assertFalse($primitive->isBoolean());
        self::assertFalse($primitive->isInteger());
        self::assertFalse($primitive->isFloat());
        self::assertFalse($primitive->isNumber());
        self::assertTrue($primitive->isJsonPrimitive());
        self::assertFalse($primitive->isJsonObject());
        self::assertFalse($primitive->isJsonArray());
        self::assertFalse($primitive->isJsonNull());

        self::assertSame('test', $primitive->asString());
    }

    public function testBooleanTrue()
    {
        $primitive = JsonPrimitive::create(true);

        self::assertFalse($primitive->isString());
        self::assertTrue($primitive->isBoolean());
        self::assertFalse($primitive->isInteger());
        self::assertFalse($primitive->isFloat());
        self::assertFalse($primitive->isNumber());
        self::assertTrue($primitive->isJsonPrimitive());
        self::assertFalse($primitive->isJsonObject());
        self::assertFalse($primitive->isJsonArray());
        self::assertFalse($primitive->isJsonNull());

        self::assertTrue($primitive->asBoolean());
    }

    public function testBooleanFalse()
    {
        $primitive = JsonPrimitive::create(false);

        self::assertFalse($primitive->isString());
        self::assertTrue($primitive->isBoolean());
        self::assertFalse($primitive->isInteger());
        self::assertFalse($primitive->isFloat());
        self::assertFalse($primitive->isNumber());
        self::assertTrue($primitive->isJsonPrimitive());
        self::assertFalse($primitive->isJsonObject());
        self::assertFalse($primitive->isJsonArray());
        self::assertFalse($primitive->isJsonNull());

        self::assertFalse($primitive->asBoolean());
    }

    public function testInteger()
    {
        $primitive = JsonPrimitive::create(1);

        self::assertFalse($primitive->isString());
        self::assertFalse($primitive->isBoolean());
        self::assertTrue($primitive->isInteger());
        self::assertFalse($primitive->isFloat());
        self::assertTrue($primitive->isNumber());
        self::assertTrue($primitive->isJsonPrimitive());
        self::assertFalse($primitive->isJsonObject());
        self::assertFalse($primitive->isJsonArray());
        self::assertFalse($primitive->isJsonNull());

        self::assertSame(1, $primitive->asInteger());
    }

    public function testFloat()
    {
        $primitive = JsonPrimitive::create(1.1);

        self::assertFalse($primitive->isString());
        self::assertFalse($primitive->isBoolean());
        self::assertFalse($primitive->isInteger());
        self::assertTrue($primitive->isFloat());
        self::assertTrue($primitive->isNumber());
        self::assertTrue($primitive->isJsonPrimitive());
        self::assertFalse($primitive->isJsonObject());
        self::assertFalse($primitive->isJsonArray());
        self::assertFalse($primitive->isJsonNull());

        self::assertSame(1.1, $primitive->asFloat());
    }

    public function testIntegerAsFloat()
    {
        $primitive = JsonPrimitive::create(1);

        self::assertSame(1.0, $primitive->asFloat());
    }

    public function testGetValue()
    {
        $primitive = JsonPrimitive::create(1);

        self::assertSame(1, $primitive->getValue());
    }

    public function testJsonSerialize()
    {
        $primitive = JsonPrimitive::create(1);

        self::assertSame('1', json_encode($primitive));
    }
}
