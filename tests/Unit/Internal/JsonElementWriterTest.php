<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
namespace Tebru\Gson\Test\Unit\Internal;

use LogicException;
use PHPUnit_Framework_TestCase;
use Tebru\Gson\Element\JsonObject;
use Tebru\Gson\Internal\JsonElementWriter;

/**
 * Class JsonElementWriterTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\JsonElementWriter
 */
class JsonElementWriterTest extends PHPUnit_Framework_TestCase
{
    public function testBeginArray()
    {
        $writer = new JsonElementWriter();
        $writer->beginArray();

        self::assertSame('[]', json_encode($writer));
    }

    public function testBeginArrayDuringObject()
    {
        $writer = new JsonElementWriter();
        $writer->beginObject();
        try {
            $writer->beginArray();
        } catch (LogicException $exception) {
            self::assertSame('Cannot call beginArray() before name() during object serialization', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testEndArray()
    {
        $writer = new JsonElementWriter();
        $writer->beginArray();
        $writer->endArray();

        self::assertSame('[]', json_encode($writer));
    }

    public function testEndArrayDuringObject()
    {
        $writer = new JsonElementWriter();
        $writer->beginObject();
        try {
            $writer->endArray();
        } catch (LogicException $exception) {
            self::assertSame('Cannot call endArray() if not serializing array', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testEndArrayFirst()
    {
        $writer = new JsonElementWriter();
        try {
            $writer->endArray();
        } catch (LogicException $exception) {
            self::assertSame('Cannot call endArray() if not serializing array', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testNestedArrays()
    {
        $writer = new JsonElementWriter();
        $writer->beginArray();
        $writer->beginArray();
        $writer->writeInteger(1);
        $writer->endArray();
        $writer->endArray();

        self::assertSame('[[1]]', json_encode($writer));
    }

    public function testBeginObject()
    {
        $writer = new JsonElementWriter();
        $writer->beginObject();

        self::assertSame('{}', json_encode($writer));
    }

    public function testBeginObjectDuringObject()
    {
        $writer = new JsonElementWriter();
        $writer->beginObject();
        try {
            $writer->beginObject();
        } catch (LogicException $exception) {
            self::assertSame('Cannot call beginObject() before name() during object serialization', $exception->getMessage());
        }
    }

    public function testEndObject()
    {
        $writer = new JsonElementWriter();
        $writer->beginObject();
        $writer->endObject();

        self::assertSame('{}', json_encode($writer));
    }

    public function testEndObjectDuringArray()
    {
        $writer = new JsonElementWriter();
        $writer->beginArray();
        try {
            $writer->endObject();
        } catch (LogicException $exception) {
            self::assertSame('Cannot call endObject() if not serializing object', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testEndObjectFirst()
    {
        $writer = new JsonElementWriter();
        try {
            $writer->endObject();
        } catch (LogicException $exception) {
            self::assertSame('Cannot call endObject() if not serializing object', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testNestedObjects()
    {
        $writer = new JsonElementWriter();
        $writer->beginObject();
        $writer->name('foo');
        $writer->beginObject();
        $writer->endObject();
        $writer->endObject();

        self::assertSame('{"foo":{}}', json_encode($writer));
    }

    public function testName()
    {
        $writer = new JsonElementWriter();
        $writer->beginObject();
        $writer->name('foo');
        $writer->writeInteger(1);
        $writer->endObject();

        self::assertSame('{"foo":1}', json_encode($writer));
    }

    public function testNameTwice()
    {
        $writer = new JsonElementWriter();
        $writer->beginObject();
        $writer->name('foo');
        try {
            $writer->name('foo');
        } catch (LogicException $exception) {
            self::assertSame('Cannot call name() at this point.  Either name() has already been called or object serialization has not been started', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testWriteInteger()
    {
        $writer = new JsonElementWriter();
        $writer->writeInteger(1);

        self::assertSame('1', json_encode($writer));
    }

    public function testWriteIntegerDuringObject()
    {
        $writer = new JsonElementWriter();
        $writer->beginObject();
        try {
            $writer->writeInteger(1);
        } catch (LogicException $exception) {
            self::assertSame('Cannot call writeInteger() before name() during object serialization', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testWriteFloat()
    {
        $writer = new JsonElementWriter();
        $writer->writeFloat(1.1);

        self::assertSame('1.1', json_encode($writer));
    }

    public function testWriteFloatNonDecimal()
    {
        $writer = new JsonElementWriter();
        $writer->writeFloat(1.0);

        self::assertSame('1', json_encode($writer));
    }

    public function testWriteFloatDuringObject()
    {
        $writer = new JsonElementWriter();
        $writer->beginObject();
        try {
            $writer->writeFloat(1);
        } catch (LogicException $exception) {
            self::assertSame('Cannot call writeFloat() before name() during object serialization', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testWriteString()
    {
        $writer = new JsonElementWriter();
        $writer->writeString('foo');

        self::assertSame('"foo"', json_encode($writer));
    }

    public function testWriteStringDuringObject()
    {
        $writer = new JsonElementWriter();
        $writer->beginObject();
        try {
            $writer->writeString('foo');
        } catch (LogicException $exception) {
            self::assertSame('Cannot call writeString() before name() during object serialization', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testWriteBoolean()
    {
        $writer = new JsonElementWriter();
        $writer->writeBoolean(true);

        self::assertSame('true', json_encode($writer));
    }

    public function testWriteBooleanFalse()
    {
        $writer = new JsonElementWriter();
        $writer->writeBoolean(false);

        self::assertSame('false', json_encode($writer));
    }

    public function testWriteBooleanDuringObject()
    {
        $writer = new JsonElementWriter();
        $writer->beginObject();
        try {
            $writer->writeBoolean(true);
        } catch (LogicException $exception) {
            self::assertSame('Cannot call writeBoolean() before name() during object serialization', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testWriteNull()
    {
        $writer = new JsonElementWriter();
        $writer->writeNull();

        self::assertSame('null', json_encode($writer));
    }

    public function testWriteNullDuringObject()
    {
        $writer = new JsonElementWriter();
        $writer->beginObject();
        $writer->name('foo');
        $writer->writeNull();

        self::assertSame('{}', json_encode($writer));
    }

    public function testWriteNullDuringObjectSerializeNulls()
    {
        $writer = new JsonElementWriter();
        $writer->setSerializeNull(true);
        $writer->beginObject();
        $writer->name('foo');
        $writer->writeNull();

        self::assertSame('{"foo":null}', json_encode($writer));
    }

    public function testWriteNullDuringArraySerializeNulls()
    {
        $writer = new JsonElementWriter();
        $writer->setSerializeNull(true);
        $writer->beginArray();
        $writer->writeNull();

        self::assertSame('[null]', json_encode($writer));
    }

    public function testWriteNullDuringBeginObject()
    {
        $writer = new JsonElementWriter();
        $writer->beginObject();
        try {
            $writer->writeNull();
        } catch (LogicException $exception) {
            self::assertSame('Cannot call writeNull() before name() during object serialization', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testWriteTwoScalars()
    {
        $writer = new JsonElementWriter();
        $writer->writeString('foo');
        try {
            $writer->writeString('bar');
        } catch (LogicException $exception) {
            self::assertSame('Attempting to write two different types', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testWriteTwice()
    {
        $writer = new JsonElementWriter();
        $writer->beginObject();
        $writer->endObject();
        try {
            $writer->writeString('bar');
        } catch (LogicException $exception) {
            self::assertSame('Attempting to write two different types', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testCanGetJsonElement()
    {
        $writer = new JsonElementWriter();
        $writer->beginObject();
        $writer->endObject();

        self::assertEquals(new JsonObject(), $writer->toJsonElement());
    }
}
