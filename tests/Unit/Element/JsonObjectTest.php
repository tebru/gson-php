<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
namespace Tebru\Gson\Test\Unit\Element;

use BadMethodCallException;
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

    public function testAsPrimitive()
    {
        $jsonObject = new JsonObject();
        $jsonObject->addString('foo', 'bar');

        self::assertSame('bar', $jsonObject->asJsonPrimitive('foo')->asString());
    }

    public function testAsPrimitiveException()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('This value is not a primitive');

        $jsonObject = new JsonObject();
        $jsonObject->add('foo', new JsonObject());

        $jsonObject->asJsonPrimitive('foo');
    }

    public function testAsArray()
    {
        $array = new JsonArray();
        $array->addInteger(1);

        $jsonObject = new JsonObject();
        $jsonObject->add('foo', $array);

        self::assertSame(1, $jsonObject->asJsonArray('foo')->get(0)->asInteger());
    }

    public function testAsArrayException()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('This value is not an array');

        $jsonObject = new JsonObject();
        $jsonObject->add('foo', new JsonObject());

        $jsonObject->asJsonArray('foo');
    }

    public function testAsObject()
    {
        $object = new JsonObject();
        $object->addInteger('bar', 1);

        $jsonObject = new JsonObject();
        $jsonObject->add('foo', $object);

        self::assertSame(1, $jsonObject->asJsonObject('foo')->get('bar')->asInteger());
    }

    public function testAsObjectException()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('This value is not an object');

        $jsonObject = new JsonObject();
        $jsonObject->add('foo', new JsonArray());

        $jsonObject->asJsonObject('foo');
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
