<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
namespace Tebru\Gson\Test\Unit\Internal;

use BadMethodCallException;
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
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Cannot call beginArray() before name() during object serialization');

        $writer = new JsonEncodeWriter();
        $writer->beginObject();
        $writer->beginArray();
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
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Cannot call endArray() if not serializing array');

        $writer = new JsonEncodeWriter();
        $writer->beginObject();
        $writer->endArray();
    }

    public function testEndArrayFirst()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Cannot call endArray() if not serializing array');

        $writer = new JsonEncodeWriter();
        $writer->endArray();
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
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Cannot call beginObject() before name() during object serialization');

        $writer = new JsonEncodeWriter();
        $writer->beginObject();
        $writer->beginObject();
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
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Cannot call endObject() if not serializing object');

        $writer = new JsonEncodeWriter();
        $writer->beginArray();
        $writer->endObject();
    }

    public function testEndObjectFirst()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Cannot call endObject() if not serializing object');

        $writer = new JsonEncodeWriter();
        $writer->endObject();
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
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Cannot call name() at this point.  Either name() has already been called or object serialization has not been started');

        $writer = new JsonEncodeWriter();
        $writer->beginObject();
        $writer->name('foo');
        $writer->name('foo');
    }

    public function testWriteInteger()
    {
        $writer = new JsonEncodeWriter();
        $writer->writeInteger(1);

        self::assertSame('1', (string) $writer);
    }

    public function testWriteIntegerDuringObject()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Cannot call writeInteger() before name() during object serialization');

        $writer = new JsonEncodeWriter();
        $writer->beginObject();
        $writer->writeInteger(1);
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
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Cannot call writeFloat() before name() during object serialization');

        $writer = new JsonEncodeWriter();
        $writer->beginObject();
        $writer->writeFloat(1);
    }

    public function testWriteString()
    {
        $writer = new JsonEncodeWriter();
        $writer->writeString('foo');

        self::assertSame('"foo"', (string) $writer);
    }

    public function testWriteStringDuringObject()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Cannot call writeString() before name() during object serialization');

        $writer = new JsonEncodeWriter();
        $writer->beginObject();
        $writer->writeString('foo');
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
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Cannot call writeBoolean() before name() during object serialization');

        $writer = new JsonEncodeWriter();
        $writer->beginObject();
        $writer->writeBoolean(true);
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
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Cannot call writeNull() before name() during object serialization');

        $writer = new JsonEncodeWriter();
        $writer->beginObject();
        $writer->writeNull();
    }

    public function testWriteTwoScalars()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Attempting to write two different types');

        $writer = new JsonEncodeWriter();
        $writer->writeString('foo');
        $writer->writeString('bar');
    }

    public function testWriteTwice()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Attempting to write two different types');

        $writer = new JsonEncodeWriter();
        $writer->beginObject();
        $writer->endObject();
        $writer->writeString('bar');
    }
}
