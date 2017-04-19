<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
namespace Tebru\Gson\Test\Unit\Element;

use PHPUnit_Framework_TestCase;
use Tebru\Gson\Element\JsonNull;
use Tebru\Gson\Exception\UnsupportedOperationException;

/**
 * Class JsonElementTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Element\JsonElement
 * @covers \Tebru\Gson\Element\JsonNull
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
        $element = new JsonNull();
        try {
            $element->asString();
        } catch (UnsupportedOperationException $exception) {
            self::assertSame('This method "asString" is not supported on "Tebru\Gson\Element\JsonNull"', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testAsInteger()
    {
        $element = new JsonNull();
        try {
            $element->asInteger();
        } catch (UnsupportedOperationException $exception) {
            self::assertSame('This method "asInteger" is not supported on "Tebru\Gson\Element\JsonNull"', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testAsFloat()
    {
        $element = new JsonNull();
        try {
            $element->asFloat();
        } catch (UnsupportedOperationException $exception) {
            self::assertSame('This method "asFloat" is not supported on "Tebru\Gson\Element\JsonNull"', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testAsBoolean()
    {
        $element = new JsonNull();
        try {
            $element->asBoolean();
        } catch (UnsupportedOperationException $exception) {
            self::assertSame('This method "asBoolean" is not supported on "Tebru\Gson\Element\JsonNull"', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testAsArray()
    {
        $element = new JsonNull();
        self::assertNull($element->jsonSerialize());
    }

    public function testAsJsonObject()
    {
        $element = new JsonNull();
        try {
            $element->asJsonObject();
        } catch (UnsupportedOperationException $exception) {
            self::assertSame('This method "asJsonObject" is not supported on "Tebru\Gson\Element\JsonNull"', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testAsJsonArray()
    {
        $element = new JsonNull();
        try {
            $element->asJsonArray();
        } catch (UnsupportedOperationException $exception) {
            self::assertSame('This method "asJsonArray" is not supported on "Tebru\Gson\Element\JsonNull"', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testGetValue()
    {
        $element = new JsonNull();
        try {
            $element->getValue();
        } catch (UnsupportedOperationException $exception) {
            self::assertSame('This method "getValue" is not supported on "Tebru\Gson\Element\JsonNull"', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }
}
