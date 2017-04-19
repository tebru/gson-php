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
 * @covers \Tebru\Gson\Internal\TypeAdapter\JsonElementTypeAdapter
 * @covers \Tebru\Gson\TypeAdapter
 */
class JsonElementTypeAdapterTest extends PHPUnit_Framework_TestCase
{
    public function testDeserializeObject()
    {
        $typeAdapter = new JsonElementTypeAdapter();
        $result = $typeAdapter->readFromJson('{"key": "value"}');

        self::assertInstanceOf(JsonObject::class, $result);
        self::assertCount(1, $result);
    }

    public function testDeserializeObjectEmpty()
    {
        $typeAdapter = new JsonElementTypeAdapter();
        $result = $typeAdapter->readFromJson('{}');

        self::assertInstanceOf(JsonObject::class, $result);
        self::assertCount(0, $result);
    }

    public function testDeserializeArray()
    {
        $typeAdapter = new JsonElementTypeAdapter();
        $result = $typeAdapter->readFromJson('[1]');

        self::assertInstanceOf(JsonArray::class, $result);
    }

    public function testDeserializeArrayEmpty()
    {
        $typeAdapter = new JsonElementTypeAdapter();
        $result = $typeAdapter->readFromJson('[]');

        self::assertInstanceOf(JsonArray::class, $result);
        self::assertCount(0, $result);
    }

    public function testDeserializeString()
    {
        $typeAdapter = new JsonElementTypeAdapter();
        $result = $typeAdapter->readFromJson('"foo"');

        self::assertInstanceOf(JsonPrimitive::class, $result);
        self::assertSame('foo', $result->asString());
    }

    public function testDeserializeInteger()
    {
        $typeAdapter = new JsonElementTypeAdapter();
        $result = $typeAdapter->readFromJson('1');

        self::assertInstanceOf(JsonPrimitive::class, $result);
        self::assertSame(1, $result->asInteger());
    }

    public function testDeserializeFloat()
    {
        $typeAdapter = new JsonElementTypeAdapter();
        $result = $typeAdapter->readFromJson('1.1');

        self::assertInstanceOf(JsonPrimitive::class, $result);
        self::assertSame(1.1, $result->asFloat());
    }

    public function testDeserializeBooleanTrue()
    {
        $typeAdapter = new JsonElementTypeAdapter();
        $result = $typeAdapter->readFromJson('true');

        self::assertInstanceOf(JsonPrimitive::class, $result);
        self::assertTrue($result->asBoolean());
    }

    public function testDeserializeBooleanFalse()
    {
        $typeAdapter = new JsonElementTypeAdapter();
        $result = $typeAdapter->readFromJson('false');

        self::assertInstanceOf(JsonPrimitive::class, $result);
        self::assertFalse($result->asBoolean());
    }

    public function testDeserializeNull()
    {
        $typeAdapter = new JsonElementTypeAdapter();
        $result = $typeAdapter->readFromJson('null');

        self::assertInstanceOf(JsonNull::class, $result);
    }

    public function testDeserializeException()
    {
        $reader = new JsonDecodeReader('{}');
        $reader->beginObject();

        $typeAdapter = new JsonElementTypeAdapter();
        try {
            $typeAdapter->read($reader);
        } catch (LogicException $exception) {
            self::assertSame('Could not handle token "end-object" at "$"', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testSerializeNull()
    {
        $typeAdapter = new JsonElementTypeAdapter();

        self::assertSame('null', $typeAdapter->writeToJson(null, false));
    }

    public function testSerializeNullObject()
    {
        $typeAdapter = new JsonElementTypeAdapter();

        self::assertSame('null', $typeAdapter->writeToJson(new JsonNull(), false));
    }

    public function testSerializeObject()
    {
        $typeAdapter = new JsonElementTypeAdapter();

        $object = new JsonObject();
        $object->addString('foo', 'bar');

        self::assertSame('{"foo":"bar"}', $typeAdapter->writeToJson($object, false));
    }

    public function testSerializeArray()
    {
        $typeAdapter = new JsonElementTypeAdapter();

        $object = new JsonArray();
        $object->addString('foo');

        self::assertSame('["foo"]', $typeAdapter->writeToJson($object, false));
    }

    public function testSerializeString()
    {
        $typeAdapter = new JsonElementTypeAdapter();

        self::assertSame('"foo"', $typeAdapter->writeToJson(JsonPrimitive::create('foo'), false));
    }

    public function testSerializeInteger()
    {
        $typeAdapter = new JsonElementTypeAdapter();

        self::assertSame('1', $typeAdapter->writeToJson(JsonPrimitive::create(1), false));
    }

    public function testSerializeFloat()
    {
        $typeAdapter = new JsonElementTypeAdapter();

        self::assertSame('1.1', $typeAdapter->writeToJson(JsonPrimitive::create(1.1), false));
    }

    public function testSerializeBooleanTrue()
    {
        $typeAdapter = new JsonElementTypeAdapter();

        self::assertSame('true', $typeAdapter->writeToJson(JsonPrimitive::create(true), false));
    }

    public function testSerializeBooleanFalse()
    {
        $typeAdapter = new JsonElementTypeAdapter();

        self::assertSame('false', $typeAdapter->writeToJson(JsonPrimitive::create(false), false));
    }

    public function testSerializeNested()
    {
        $typeAdapter = new JsonElementTypeAdapter();

        $jsonObject = new JsonObject();
        $jsonObject->addString('foo', 'bar');

        $jsonArray = new JsonArray();
        $jsonArray->addString('foo');
        $jsonArray->addJsonElement(new JsonNull());

        $jsonObject2 = new JsonObject();
        $jsonObject2->add('foo', new JsonNull());

        $jsonArray->addJsonElement($jsonObject2);
        $jsonObject->add('array', $jsonArray);

        self::assertSame('{"foo":"bar","array":["foo",null,{"foo":null}]}', $typeAdapter->writeToJson($jsonObject, true));
    }
}
