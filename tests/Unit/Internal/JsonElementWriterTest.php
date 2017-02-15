<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
namespace Tebru\Gson\Test\Unit\Internal;

use BadMethodCallException;
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
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Cannot call beginArray() before name() during object serialization');

        $writer = new JsonElementWriter();
        $writer->beginObject();
        $writer->beginArray();
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
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Cannot call endArray() if not serializing array');

        $writer = new JsonElementWriter();
        $writer->beginObject();
        $writer->endArray();
    }

    public function testEndArrayFirst()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Cannot call endArray() if not serializing array');

        $writer = new JsonElementWriter();
        $writer->endArray();
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
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Cannot call beginObject() before name() during object serialization');

        $writer = new JsonElementWriter();
        $writer->beginObject();
        $writer->beginObject();
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
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Cannot call endObject() if not serializing object');

        $writer = new JsonElementWriter();
        $writer->beginArray();
        $writer->endObject();
    }

    public function testEndObjectFirst()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Cannot call endObject() if not serializing object');

        $writer = new JsonElementWriter();
        $writer->endObject();
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
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Cannot call name() at this point.  Either name() has already been called or object serialization has not been started');

        $writer = new JsonElementWriter();
        $writer->beginObject();
        $writer->name('foo');
        $writer->name('foo');
    }

    public function testWriteInteger()
    {
        $writer = new JsonElementWriter();
        $writer->writeInteger(1);

        self::assertSame('1', json_encode($writer));
    }

    public function testWriteIntegerDuringObject()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Cannot call writeInteger() before name() during object serialization');

        $writer = new JsonElementWriter();
        $writer->beginObject();
        $writer->writeInteger(1);
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
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Cannot call writeFloat() before name() during object serialization');

        $writer = new JsonElementWriter();
        $writer->beginObject();
        $writer->writeFloat(1);
    }

    public function testWriteString()
    {
        $writer = new JsonElementWriter();
        $writer->writeString('foo');

        self::assertSame('"foo"', json_encode($writer));
    }

    public function testWriteStringDuringObject()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Cannot call writeString() before name() during object serialization');

        $writer = new JsonElementWriter();
        $writer->beginObject();
        $writer->writeString('foo');
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
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Cannot call writeBoolean() before name() during object serialization');

        $writer = new JsonElementWriter();
        $writer->beginObject();
        $writer->writeBoolean(true);
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
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Cannot call writeNull() before name() during object serialization');

        $writer = new JsonElementWriter();
        $writer->beginObject();
        $writer->writeNull();
    }

    public function testWriteTwoScalars()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Attempting to write two different types');

        $writer = new JsonElementWriter();
        $writer->writeString('foo');
        $writer->writeString('bar');
    }

    public function testWriteTwice()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Attempting to write two different types');

        $writer = new JsonElementWriter();
        $writer->beginObject();
        $writer->endObject();
        $writer->writeString('bar');
    }

    public function testCanGetJsonElement()
    {
        $writer = new JsonElementWriter();
        $writer->beginObject();
        $writer->endObject();

        self::assertEquals(new JsonObject(), $writer->toJsonElement());
    }
}
