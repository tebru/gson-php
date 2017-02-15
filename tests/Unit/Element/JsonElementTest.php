<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
namespace Tebru\Gson\Test\Unit\Element;

use PHPUnit_Framework_TestCase;
use Tebru\Gson\Element\JsonNull;
use Tebru\Gson\Exception\UnsupportedMethodException;

/**
 * Class JsonElementTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Element\JsonElement
 */
class JsonElementTest extends PHPUnit_Framework_TestCase
{

    public function testIsMethods()
    {
        $element = new JsonNull();

        self::assertFalse($element->isJsonObject());
        self::assertFalse($element->isJsonArray());
        self::assertFalse($element->isJsonPrimitive());
        self::assertTrue($element->isJsonNull());

        self::assertFalse($element->isString());
        self::assertFalse($element->isInteger());
        self::assertFalse($element->isFloat());
        self::assertFalse($element->isNumber());
        self::assertFalse($element->isBoolean());
    }

    public function testAsString()
    {
        $this->expectException(UnsupportedMethodException::class);
        $this->expectExceptionMessage('This method "asString" is not supported on "Tebru\Gson\Element\JsonNull"');

        $element = new JsonNull();
        $element->asString();
    }

    public function testAsInteger()
    {
        $this->expectException(UnsupportedMethodException::class);
        $this->expectExceptionMessage('This method "asInteger" is not supported on "Tebru\Gson\Element\JsonNull"');

        $element = new JsonNull();
        $element->asInteger();
    }

    public function testAsFloat()
    {
        $this->expectException(UnsupportedMethodException::class);
        $this->expectExceptionMessage('This method "asFloat" is not supported on "Tebru\Gson\Element\JsonNull"');

        $element = new JsonNull();
        $element->asFloat();
    }

    public function testAsBoolean()
    {
        $this->expectException(UnsupportedMethodException::class);
        $this->expectExceptionMessage('This method "asBoolean" is not supported on "Tebru\Gson\Element\JsonNull"');

        $element = new JsonNull();
        $element->asBoolean();
    }

    public function testAsArray()
    {
        $this->expectException(UnsupportedMethodException::class);
        $this->expectExceptionMessage('This method "asArray" is not supported on "Tebru\Gson\Element\JsonNull"');

        $element = new JsonNull();
        $element->asArray();
    }

    public function testAsJsonObject()
    {
        $this->expectException(UnsupportedMethodException::class);
        $this->expectExceptionMessage('This method "asJsonObject" is not supported on "Tebru\Gson\Element\JsonNull"');

        $element = new JsonNull();
        $element->asJsonObject();
    }

    public function testAsJsonArray()
    {
        $this->expectException(UnsupportedMethodException::class);
        $this->expectExceptionMessage('This method "asJsonArray" is not supported on "Tebru\Gson\Element\JsonNull"');

        $element = new JsonNull();
        $element->asJsonArray();
    }

    public function testGetValue()
    {
        $this->expectException(UnsupportedMethodException::class);
        $this->expectExceptionMessage('This method "getValue" is not supported on "Tebru\Gson\Element\JsonNull"');

        $element = new JsonNull();
        $element->getValue();
    }
}
