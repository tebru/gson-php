<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
namespace Tebru\Gson\Test\Unit\Element;

use LogicException;
use PHPUnit\Framework\TestCase;
use Tebru\Gson\Element\JsonArray;
use Tebru\Gson\Element\JsonObject;

/**
 * Class JsonObjectTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Element\JsonObject
 */
class JsonObjectTest extends TestCase
{
    public function testAddString(): void
    {
        $jsonObject = new JsonObject();
        $jsonObject->addString('foo', 'test');

        self::assertSame('test', $jsonObject->get('foo')->asString());
    }

    public function testAddInteger(): void
    {
        $jsonObject = new JsonObject();
        $jsonObject->addInteger('foo', 1);

        self::assertSame(1, $jsonObject->get('foo')->asInteger());
    }

    public function testAddFloat(): void
    {
        $jsonObject = new JsonObject();
        $jsonObject->addFloat('foo', 1.1);

        self::assertSame(1.1, $jsonObject->get('foo')->asFloat());
    }

    public function testAddBoolean(): void
    {
        $jsonObject = new JsonObject();
        $jsonObject->addBoolean('foo', true);

        self::assertTrue($jsonObject->get('foo')->asBoolean());
    }

    public function testAddBooleanFalse(): void
    {
        $jsonObject = new JsonObject();
        $jsonObject->addBoolean('foo', false);

        self::assertFalse($jsonObject->get('foo')->asBoolean());
    }

    public function testAddArray(): void
    {
        $array = new JsonArray();
        $array->addInteger(1);
        $jsonObject = new JsonObject();
        $jsonObject->add('foo', $array);

        self::assertSame($array, $jsonObject->get('foo'));
    }

    public function testAddObject(): void
    {
        $object = new JsonObject();
        $object->addInteger('bar', 1);
        $jsonObject = new JsonObject();
        $jsonObject->add('foo', $object);

        self::assertSame($object, $jsonObject->get('foo'));
    }

    public function testHas(): void
    {
        $jsonObject = new JsonObject();
        $jsonObject->addString('foo', 'bar');

        self::assertTrue($jsonObject->has('foo'));
        self::assertFalse($jsonObject->has('bar'));
    }

    public function testGet(): void
    {
        $jsonObject = new JsonObject();
        $jsonObject->addString('foo', 'bar');

        self::assertSame('bar', $jsonObject->get('foo')->asString());
    }

    public function testGetAsPrimitive(): void
    {
        $jsonObject = new JsonObject();
        $jsonObject->addString('foo', 'bar');

        self::assertSame('bar', $jsonObject->getAsJsonPrimitive('foo')->asString());
    }

    public function testGetAsPrimitiveException(): void
    {
        $jsonObject = new JsonObject();
        $jsonObject->add('foo', new JsonObject());

        try {
            $jsonObject->getAsJsonPrimitive('foo');
        } catch (LogicException $exception) {
            self::assertSame('This value is not a primitive', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testGetAsJsonArray(): void
    {
        $array = new JsonArray();
        $array->addInteger(1);

        $jsonObject = new JsonObject();
        $jsonObject->add('foo', $array);

        self::assertSame(1, $jsonObject->getAsJsonArray('foo')->get(0)->asInteger());
    }

    public function testGetAsJsonArrayException(): void
    {
        $jsonObject = new JsonObject();
        $jsonObject->add('foo', new JsonObject());

        try {
            $jsonObject->getAsJsonArray('foo');
        } catch (LogicException $exception) {
            self::assertSame('This value is not an array', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testGetAsJsonObject(): void
    {
        $object = new JsonObject();
        $object->addInteger('bar', 1);

        $jsonObject = new JsonObject();
        $jsonObject->add('foo', $object);

        self::assertSame(1, $jsonObject->getAsJsonObject('foo')->get('bar')->asInteger());
    }

    public function testGetAsJsonObjectException(): void
    {
        $jsonObject = new JsonObject();
        $jsonObject->add('foo', new JsonArray());

        try {
            $jsonObject->getAsJsonObject('foo');
        } catch (LogicException $exception) {
            self::assertSame('This value is not an object', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testGetAsString(): void
    {
        $jsonObject = new JsonObject();
        $jsonObject->addString('foo', 'bar');

        self::assertSame('bar', $jsonObject->getAsString('foo'));
    }

    public function testGetAsInteger(): void
    {
        $jsonObject = new JsonObject();
        $jsonObject->addInteger('foo', 1);

        self::assertSame(1, $jsonObject->getAsInteger('foo'));
    }

    public function testGetAsFloat(): void
    {
        $jsonObject = new JsonObject();
        $jsonObject->addFloat('foo', 1);

        self::assertSame(1.0, $jsonObject->getAsFloat('foo'));
    }

    public function testGetAsBoolean(): void
    {
        $jsonObject = new JsonObject();
        $jsonObject->addBoolean('foo', false);

        self::assertFalse($jsonObject->getAsBoolean('foo'));
    }

    public function testGetAsArray(): void
    {
        $object = new JsonObject();
        $object->addString('foo', 'bar');
        $jsonObject = new JsonObject();
        $jsonObject->add('foo', $object);

        self::assertSame(['foo' => 'bar'], $jsonObject->getAsArray('foo'));
    }

    public function testAsJsonObject(): void
    {
        $jsonObject = new JsonObject();
        $jsonObject->addBoolean('foo', false);

        self::assertSame($jsonObject, $jsonObject->asJsonObject());
    }

    public function testAsArray(): void
    {
        $array = new JsonArray();
        $array->addInteger(1);
        $object = new JsonObject();
        $object->add('bar', $array);
        $jsonObject = new JsonObject();
        $jsonObject->add('foo', $object);

        self::assertSame(json_encode(['foo' => ['bar' => [1]]]), json_encode($jsonObject->jsonSerialize()));
    }

    public function testRemove(): void
    {
        $jsonObject = new JsonObject();
        $jsonObject->addString('foo', 'bar');

        self::assertTrue($jsonObject->remove('foo'));
        self::assertFalse($jsonObject->remove('foo'));
    }

    public function testCount(): void
    {
        $jsonObject = new JsonObject();
        $jsonObject->addString('foo', 'bar');

        self::assertCount(1, $jsonObject);
    }

    public function testGetIterator(): void
    {
        $jsonObject = new JsonObject();
        $jsonObject->addString('foo', 'bar');

        foreach ($jsonObject as $key => $value) {
            self::assertSame('foo', $key);
            self::assertSame('bar', $value->asString());
        }
    }
}
