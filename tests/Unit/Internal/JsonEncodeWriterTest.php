<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
namespace Tebru\Gson\Test\Unit\Internal;

use LogicException;
use PHPUnit_Framework_TestCase;
use Tebru\Gson\Internal\JsonEncodeWriter;

/**
 * Class JsonEncodeWriterTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\JsonEncodeWriter
 */
class JsonEncodeWriterTest extends PHPUnit_Framework_TestCase
{
    public function testBeginArray()
    {
        $writer = new JsonEncodeWriter();
        $writer->beginArray();

        self::assertSame('[]', (string) $writer);
    }

    public function testBeginArrayDuringObject()
    {
        $writer = new JsonEncodeWriter();
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
        $writer = new JsonEncodeWriter();
        $writer->beginArray();
        $writer->endArray();

        self::assertSame('[]', (string) $writer);
    }

    public function testEndArrayDuringObject()
    {
        $writer = new JsonEncodeWriter();
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
        $writer = new JsonEncodeWriter();
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
        $writer = new JsonEncodeWriter();
        $writer->beginArray();
        $writer->beginArray();
        $writer->writeInteger(1);
        $writer->endArray();
        $writer->endArray();

        self::assertSame('[[1]]', (string) $writer);
    }

    public function testBeginObject()
    {
        $writer = new JsonEncodeWriter();
        $writer->beginObject();

        self::assertSame('{}', (string) $writer);
    }

    public function testBeginObjectDuringObject()
    {
        $writer = new JsonEncodeWriter();
        $writer->beginObject();
        try {
            $writer->beginObject();
        } catch (LogicException $exception) {
            self::assertSame('Cannot call beginObject() before name() during object serialization', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testEndObject()
    {
        $writer = new JsonEncodeWriter();
        $writer->beginObject();
        $writer->endObject();

        self::assertSame('{}', (string) $writer);
    }

    public function testEndObjectDuringArray()
    {
        $writer = new JsonEncodeWriter();
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
        $writer = new JsonEncodeWriter();
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
        $writer = new JsonEncodeWriter();
        $writer->beginObject();
        $writer->name('foo');
        $writer->beginObject();
        $writer->endObject();
        $writer->endObject();

        self::assertSame('{"foo":{}}', (string) $writer);
    }

    public function testName()
    {
        $writer = new JsonEncodeWriter();
        $writer->beginObject();
        $writer->name('foo');
        $writer->writeInteger(1);
        $writer->endObject();

        self::assertSame('{"foo":1}', (string) $writer);
    }

    public function testNameTwice()
    {
        $writer = new JsonEncodeWriter();
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
        $writer = new JsonEncodeWriter();
        $writer->writeInteger(1);

        self::assertSame('1', (string) $writer);
    }

    public function testWriteIntegerDuringObject()
    {
        $writer = new JsonEncodeWriter();
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
        $writer = new JsonEncodeWriter();
        $writer->writeFloat(1.1);

        self::assertSame('1.1', (string) $writer);
    }

    public function testWriteFloatNonDecimal()
    {
        $writer = new JsonEncodeWriter();
        $writer->writeFloat(1.0);

        self::assertSame('1', (string) $writer);
    }

    public function testWriteFloatDuringObject()
    {
        $writer = new JsonEncodeWriter();
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
        $writer = new JsonEncodeWriter();
        $writer->writeString('foo');

        self::assertSame('"foo"', (string) $writer);
    }

    public function testWriteStringDuringObject()
    {
        $writer = new JsonEncodeWriter();
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
        $writer = new JsonEncodeWriter();
        $writer->writeBoolean(true);

        self::assertSame('true', (string) $writer);
    }

    public function testWriteBooleanFalse()
    {
        $writer = new JsonEncodeWriter();
        $writer->writeBoolean(false);

        self::assertSame('false', (string) $writer);
    }

    public function testWriteBooleanDuringObject()
    {
        $writer = new JsonEncodeWriter();
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
        $writer = new JsonEncodeWriter();
        $writer->writeNull();

        self::assertSame('null', (string) $writer);
    }

    public function testWriteNullDuringObject()
    {
        $writer = new JsonEncodeWriter();
        $writer->beginObject();
        $writer->name('foo');
        $writer->writeNull();

        self::assertSame('{}', (string) $writer);
    }

    public function testWriteNullDuringObjectSerializeNulls()
    {
        $writer = new JsonEncodeWriter();
        $writer->setSerializeNull(true);
        $writer->beginObject();
        $writer->name('foo');
        $writer->writeNull();

        self::assertSame('{"foo":null}', (string) $writer);
    }

    public function testWriteNullDuringArraySerializeNulls()
    {
        $writer = new JsonEncodeWriter();
        $writer->setSerializeNull(true);
        $writer->beginArray();
        $writer->writeNull();

        self::assertSame('[null]', (string) $writer);
    }

    public function testWriteNullDuringBeginObject()
    {
        $writer = new JsonEncodeWriter();
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
        $writer = new JsonEncodeWriter();
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
        $writer = new JsonEncodeWriter();
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
}
