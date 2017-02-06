<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter;

use LogicException;
use PHPUnit_Framework_TestCase;
use Tebru\Gson\Element\JsonArray;
use Tebru\Gson\Element\JsonNull;
use Tebru\Gson\Element\JsonObject;
use Tebru\Gson\Element\JsonPrimitive;
use Tebru\Gson\Internal\JsonDecodeReader;
use Tebru\Gson\Internal\TypeAdapter\JsonElementTypeAdapter;

/**
 * Class JsonElementTypeAdapterTest
 *
 * @author Nate Brunette <n@tebru.net>
 */
class JsonElementTypeAdapterTest extends PHPUnit_Framework_TestCase
{
    public function testObject()
    {
        $typeAdapter = new JsonElementTypeAdapter();
        $result = $typeAdapter->readFromJson('{"key": "value"}');

        self::assertInstanceOf(JsonObject::class, $result);
        self::assertCount(1, $result);
    }

    public function testObjectEmpty()
    {
        $typeAdapter = new JsonElementTypeAdapter();
        $result = $typeAdapter->readFromJson('{}');

        self::assertInstanceOf(JsonObject::class, $result);
        self::assertCount(0, $result);
    }

    public function testArray()
    {
        $typeAdapter = new JsonElementTypeAdapter();
        $result = $typeAdapter->readFromJson('[1]');

        self::assertInstanceOf(JsonArray::class, $result);
    }

    public function testArrayEmpty()
    {
        $typeAdapter = new JsonElementTypeAdapter();
        $result = $typeAdapter->readFromJson('[]');

        self::assertInstanceOf(JsonArray::class, $result);
        self::assertCount(0, $result);
    }

    public function testString()
    {
        $typeAdapter = new JsonElementTypeAdapter();
        $result = $typeAdapter->readFromJson('"foo"');

        self::assertInstanceOf(JsonPrimitive::class, $result);
        self::assertSame('foo', $result->asString());
    }

    public function testInteger()
    {
        $typeAdapter = new JsonElementTypeAdapter();
        $result = $typeAdapter->readFromJson('1');

        self::assertInstanceOf(JsonPrimitive::class, $result);
        self::assertSame(1, $result->asInteger());
    }

    public function testFloat()
    {
        $typeAdapter = new JsonElementTypeAdapter();
        $result = $typeAdapter->readFromJson('1.1');

        self::assertInstanceOf(JsonPrimitive::class, $result);
        self::assertSame(1.1, $result->asFloat());
    }

    public function testBooleanTrue()
    {
        $typeAdapter = new JsonElementTypeAdapter();
        $result = $typeAdapter->readFromJson('true');

        self::assertInstanceOf(JsonPrimitive::class, $result);
        self::assertTrue($result->asBoolean());
    }

    public function testBooleanFalse()
    {
        $typeAdapter = new JsonElementTypeAdapter();
        $result = $typeAdapter->readFromJson('false');

        self::assertInstanceOf(JsonPrimitive::class, $result);
        self::assertFalse($result->asBoolean());
    }

    public function testNull()
    {
        $typeAdapter = new JsonElementTypeAdapter();
        $result = $typeAdapter->readFromJson('null');

        self::assertInstanceOf(JsonNull::class, $result);
    }

    public function testException()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Could not handle token "end-object"');

        $reader = new JsonDecodeReader('{}');
        $reader->beginObject();

        $typeAdapter = new JsonElementTypeAdapter();
        $typeAdapter->read($reader);
    }
}
