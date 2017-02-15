<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
namespace Tebru\Gson\Test\Unit\Element;

use PHPUnit_Framework_TestCase;
use Tebru\Gson\Element\JsonArray;
use Tebru\Gson\Element\JsonObject;
use Tebru\Gson\Element\JsonPrimitive;

/**
 * Class JsonArrayTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Element\JsonArray
 */
class JsonArrayTest extends PHPUnit_Framework_TestCase
{
    public function testAddString()
    {
        $jsonArray = new JsonArray();
        $jsonArray->addString('test');

        self::assertTrue($jsonArray->get(0)->isString());
    }

    public function testAddInteger()
    {
        $jsonArray = new JsonArray();
        $jsonArray->addInteger(1);

        self::assertTrue($jsonArray->get(0)->isInteger());
    }

    public function testAddFloat()
    {
        $jsonArray = new JsonArray();
        $jsonArray->addFloat(1.1);

        self::assertTrue($jsonArray->get(0)->isFloat());
    }

    public function testAddIntegerAsFloat()
    {
        $jsonArray = new JsonArray();
        $jsonArray->addFloat(1);

        self::assertTrue($jsonArray->get(0)->isFloat());
    }

    public function testAddBoolean()
    {
        $jsonArray = new JsonArray();
        $jsonArray->addBoolean(true);

        self::assertTrue($jsonArray->get(0)->isBoolean());
    }

    public function testAddObject()
    {
        $jsonArray = new JsonArray();
        $jsonArray->addJsonElement(new JsonObject());

        self::assertTrue($jsonArray->get(0)->isJsonObject());
    }

    public function testAddArray()
    {
        $jsonArray = new JsonArray();
        $jsonArray2 = new JsonArray();
        $jsonArray2->addInteger(1);
        $jsonArray->addAll($jsonArray2);

        self::assertTrue($jsonArray->get(0)->isInteger());
        self::assertCount(1, $jsonArray);
    }

    public function testContainsTrue()
    {
        $jsonObject = new JsonObject();
        $jsonObject->addInteger('foo', 1);
        $jsonArray = new JsonArray();
        $jsonArray->addJsonElement($jsonObject);

        self::assertTrue($jsonArray->contains($jsonObject));
    }

    public function testContainsFalse()
    {
        $jsonObject = new JsonObject();
        $jsonObject->addInteger('foo', 1);
        $jsonArray = new JsonArray();
        $jsonArray->addJsonElement($jsonObject);
        $jsonObject2 = new JsonObject();
        $jsonObject2->addInteger('foo', 1);

        self::assertFalse($jsonArray->contains($jsonObject2));
    }

    public function testHas()
    {
        $jsonArray = new JsonArray();
        $jsonArray->addInteger(1);

        self::assertTrue($jsonArray->has(0));
        self::assertFalse($jsonArray->has(1));
    }

    public function testGet()
    {
        $jsonArray = new JsonArray();
        $jsonArray->addInteger(1);

        self::assertSame(1, $jsonArray->get(0)->asInteger());
    }

    public function testSet()
    {
        $primitive = JsonPrimitive::create(2);
        $jsonArray = new JsonArray();
        $jsonArray->addInteger(1);
        $jsonArray->set(0, $primitive);

        self::assertSame(2, $jsonArray->get(0)->asInteger());
    }

    public function testRemove()
    {
        $jsonObject = new JsonObject();
        $jsonObject->addInteger('foo', 1);
        $jsonArray = new JsonArray();
        $jsonArray->addJsonElement($jsonObject);

        self::assertTrue($jsonArray->remove($jsonObject));
        self::assertFalse($jsonArray->remove($jsonObject));
    }

    public function testCount()
    {
        $jsonArray = new JsonArray();
        $jsonArray->addInteger(1);

        self::assertCount(1, $jsonArray);
    }

    public function testAsJsonArray()
    {
        $jsonArray = new JsonArray();
        $result = $jsonArray->asJsonArray();

        self::assertSame($jsonArray, $result);
    }

    public function testJsonSerialize()
    {
        $object = new JsonObject();
        $object->addFloat('float', 2);

        $array = new JsonArray();
        $array->addInteger(1);
        $array->addBoolean(true);
        $array->addJsonElement($object);

        $jsonArray = new JsonArray();
        $jsonArray->addInteger(1);
        $jsonArray->addJsonElement($array);

        $expected = [1, [1, true, ['float' => 2.0]]];

        self::assertSame(json_encode($expected), json_encode($jsonArray->jsonSerialize()));
    }
}


