<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
namespace Tebru\Gson\Test\Unit\Element;

use LogicException;
use PHPUnit_Framework_TestCase;
use Tebru\Gson\Element\JsonArray;
use Tebru\Gson\Element\JsonObject;

/**
 * Class JsonObjectTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Element\JsonObject
 */
class JsonObjectTest extends PHPUnit_Framework_TestCase
{
    public function testAddString()
    {
        $jsonObject = new JsonObject();
        $jsonObject->addString('foo', 'test');

        self::assertSame('test', $jsonObject->get('foo')->asString());
    }

    public function testAddInteger()
    {
        $jsonObject = new JsonObject();
        $jsonObject->addInteger('foo', 1);

        self::assertSame(1, $jsonObject->get('foo')->asInteger());
    }

    public function testAddFloat()
    {
        $jsonObject = new JsonObject();
        $jsonObject->addFloat('foo', 1.1);

        self::assertSame(1.1, $jsonObject->get('foo')->asFloat());
    }

    public function testAddBoolean()
    {
        $jsonObject = new JsonObject();
        $jsonObject->addBoolean('foo', true);

        self::assertTrue($jsonObject->get('foo')->asBoolean());
    }

    public function testAddBooleanFalse()
    {
        $jsonObject = new JsonObject();
        $jsonObject->addBoolean('foo', false);

        self::assertFalse($jsonObject->get('foo')->asBoolean());
    }

    public function testAddArray()
    {
        $array = new JsonArray();
        $array->addInteger(1);
        $jsonObject = new JsonObject();
        $jsonObject->add('foo', $array);

        self::assertSame($array, $jsonObject->get('foo'));
    }

    public function testAddObject()
    {
        $object = new JsonObject();
        $object->addInteger('bar', 1);
        $jsonObject = new JsonObject();
        $jsonObject->add('foo', $object);

        self::assertSame($object, $jsonObject->get('foo'));
    }

    public function testHas()
    {
        $jsonObject = new JsonObject();
        $jsonObject->addString('foo', 'bar');

        self::assertTrue($jsonObject->has('foo'));
        self::assertFalse($jsonObject->has('bar'));
    }

    public function testGet()
    {
        $jsonObject = new JsonObject();
        $jsonObject->addString('foo', 'bar');

        self::assertSame('bar', $jsonObject->get('foo')->asString());
    }

    public function testGetAsPrimitive()
    {
        $jsonObject = new JsonObject();
        $jsonObject->addString('foo', 'bar');

        self::assertSame('bar', $jsonObject->getAsJsonPrimitive('foo')->asString());
    }

    public function testGetAsPrimitiveException()
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

    public function testGetAsJsonArray()
    {
        $array = new JsonArray();
        $array->addInteger(1);

        $jsonObject = new JsonObject();
        $jsonObject->add('foo', $array);

        self::assertSame(1, $jsonObject->getAsJsonArray('foo')->get(0)->asInteger());
    }

    public function testGetAsJsonArrayException()
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

    public function testGetAsJsonObject()
    {
        $object = new JsonObject();
        $object->addInteger('bar', 1);

        $jsonObject = new JsonObject();
        $jsonObject->add('foo', $object);

        self::assertSame(1, $jsonObject->getAsJsonObject('foo')->get('bar')->asInteger());
    }

    public function testGetAsJsonObjectException()
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

    public function testGetAsString()
    {
        $jsonObject = new JsonObject();
        $jsonObject->addString('foo', 'bar');

        self::assertSame('bar', $jsonObject->getAsString('foo'));
    }

    public function testGetAsInteger()
    {
        $jsonObject = new JsonObject();
        $jsonObject->addInteger('foo', 1);

        self::assertSame(1, $jsonObject->getAsInteger('foo'));
    }

    public function testGetAsFloat()
    {
        $jsonObject = new JsonObject();
        $jsonObject->addFloat('foo', 1);

        self::assertSame(1.0, $jsonObject->getAsFloat('foo'));
    }

    public function testGetAsBoolean()
    {
        $jsonObject = new JsonObject();
        $jsonObject->addBoolean('foo', false);

        self::assertFalse($jsonObject->getAsBoolean('foo'));
    }

    public function testGetAsArray()
    {
        $object = new JsonObject();
        $object->addString('foo', 'bar');
        $jsonObject = new JsonObject();
        $jsonObject->add('foo', $object);

        self::assertSame(['foo' => 'bar'], $jsonObject->getAsArray('foo'));
    }

    public function testAsJsonObject()
    {
        $jsonObject = new JsonObject();
        $jsonObject->addBoolean('foo', false);

        self::assertSame($jsonObject, $jsonObject->asJsonObject());
    }

    public function testAsArray()
    {
        $array = new JsonArray();
        $array->addInteger(1);
        $object = new JsonObject();
        $object->add('bar', $array);
        $jsonObject = new JsonObject();
        $jsonObject->add('foo', $object);

        self::assertSame(json_encode(['foo' => ['bar' => [1]]]), json_encode($jsonObject->jsonSerialize()));
    }

    public function testRemove()
    {
        $jsonObject = new JsonObject();
        $jsonObject->addString('foo', 'bar');

        self::assertTrue($jsonObject->remove('foo'));
        self::assertFalse($jsonObject->remove('foo'));
    }

    public function testCount()
    {
        $jsonObject = new JsonObject();
        $jsonObject->addString('foo', 'bar');

        self::assertCount(1, $jsonObject);
    }

    public function testGetIterator()
    {
        $jsonObject = new JsonObject();
        $jsonObject->addString('foo', 'bar');

        foreach ($jsonObject as $key => $value) {
            self::assertSame('foo', $key);
            self::assertSame('bar', $value->asString());
        }
    }
}
