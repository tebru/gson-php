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
 * @covers \Tebru\Gson\Internal\JsonWriter
 * @covers \Tebru\Gson\Internal\JsonEncodeWriter
 * @covers \Tebru\Gson\Internal\JsonPath
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
            self::assertSame('Cannot call beginArray() before name() during object serialization at "$"', $exception->getMessage());
            return;
        }
        self::fail('Failed to throw exception');
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
            self::assertSame('Cannot call endArray() if not serializing array at "$"', $exception->getMessage());
            return;
        }
        self::fail('Failed to throw exception');
    }

    public function testEndArrayFirst()
    {
        $writer = new JsonEncodeWriter();
        try {
            $writer->endArray();
        } catch (LogicException $exception) {
            self::assertSame('Cannot call endArray() if not serializing array at "$"', $exception->getMessage());
            return;
        }
        self::fail('Failed to throw exception');
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
            self::assertSame('Cannot call beginObject() before name() during object serialization at "$"', $exception->getMessage());
            return;
        }
        self::fail('Failed to throw exception');
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
            self::assertSame('Cannot call endObject() if not serializing object at "$"', $exception->getMessage());
            return;
        }
        self::fail('Failed to throw exception');
    }

    public function testEndObjectFirst()
    {
        $writer = new JsonEncodeWriter();
        try {
            $writer->endObject();
        } catch (LogicException $exception) {
            self::assertSame('Cannot call endObject() if not serializing object at "$"', $exception->getMessage());
            return;
        }
        self::fail('Failed to throw exception');
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
            self::assertSame('Cannot call name() at this point.  Either name() has already been called or object serialization has not been started at "$.foo"', $exception->getMessage());
            return;
        }
        self::fail('Failed to throw exception');
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
            self::assertSame('Cannot call writeInteger() before name() during object serialization at "$"', $exception->getMessage());
            return;
        }
        self::fail('Failed to throw exception');
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
            self::assertSame('Cannot call writeFloat() before name() during object serialization at "$"', $exception->getMessage());
            return;
        }
        self::fail('Failed to throw exception');
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
            self::assertSame('Cannot call writeString() before name() during object serialization at "$"', $exception->getMessage());
            return;
        }
        self::fail('Failed to throw exception');
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
            self::assertSame('Cannot call writeBoolean() before name() during object serialization at "$"', $exception->getMessage());
            return;
        }
        self::fail('Failed to throw exception');
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
            self::assertSame('Cannot call writeNull() before name() during object serialization at "$"', $exception->getMessage());
            return;
        }
        self::fail('Failed to throw exception');
    }

    public function testWriteTwoScalars()
    {
        $writer = new JsonEncodeWriter();
        $writer->writeString('foo');
        try {
            $writer->writeString('bar');
        } catch (LogicException $exception) {
            self::assertSame('Attempting to write two different types at "$"', $exception->getMessage());
            return;
        }
        self::fail('Failed to throw exception');
    }

    public function testWriteTwice()
    {
        $writer = new JsonEncodeWriter();
        $writer->beginObject();
        $writer->endObject();
        try {
            $writer->writeString('bar');
        } catch (LogicException $exception) {
            self::assertSame('Attempting to write two different types at "$"', $exception->getMessage());
            return;
        }
        self::fail('Failed to throw exception');
    }

    public function testPathBeginObject()
    {
        $writer = new JsonEncodeWriter();
        $writer->beginObject();
        self::assertSame('$', $writer->getPath());
    }

    public function testPathName()
    {
        $writer = new JsonEncodeWriter();
        $writer->beginObject();
        $writer->name('foo');
        self::assertSame('$.foo', $writer->getPath());
    }

    public function testPathObjectValue()
    {
        $writer = new JsonEncodeWriter();
        $writer->beginObject();
        $writer->name('foo');
        $writer->writeInteger(1);
        self::assertSame('$.foo', $writer->getPath());
    }

    public function testPathObjectSecondValue()
    {
        $writer = new JsonEncodeWriter();
        $writer->beginObject();
        $writer->name('foo');
        $writer->writeInteger(1);
        $writer->name('bar');
        $writer->writeBoolean(true);
        self::assertSame('$.bar', $writer->getPath());
    }

    public function testPathObjectSerializeNull()
    {
        $writer = new JsonEncodeWriter();
        $writer->setSerializeNull(true);
        $writer->beginObject();
        $writer->name('foo');
        $writer->writeInteger(1);
        $writer->name('bar');
        $writer->writeNull();
        self::assertSame('$.bar', $writer->getPath());
    }

    public function testPathObjectNotSerializeNull()
    {
        $writer = new JsonEncodeWriter();
        $writer->setSerializeNull(false);
        $writer->beginObject();
        $writer->name('foo');
        $writer->writeInteger(1);
        $writer->name('bar');
        $writer->writeNull();

        // even though we're not serializing nulls, we need to keep track of the place
        // this is different than arrays where we pretend the value doesn't exist
        self::assertSame('$.bar', $writer->getPath());
    }

    public function testPathObjectInObject()
    {
        $writer = new JsonEncodeWriter();
        $writer->beginObject();
        $writer->name('foo');
        $writer->beginObject();
        self::assertSame('$.foo', $writer->getPath());
    }

    public function testPathObjectInObjectValue()
    {
        $writer = new JsonEncodeWriter();
        $writer->beginObject();
        $writer->name('foo');
        $writer->beginObject();
        $writer->name('bar');
        self::assertSame('$.foo.bar', $writer->getPath());
    }

    public function testPathArrayInObject()
    {
        $writer = new JsonEncodeWriter();
        $writer->beginObject();
        $writer->name('foo');
        $writer->beginArray();
        self::assertSame('$.foo', $writer->getPath());
    }

    public function testPathArrayInObjectValue()
    {
        $writer = new JsonEncodeWriter();
        $writer->beginObject();
        $writer->name('foo');
        $writer->beginArray();
        $writer->writeInteger(1);
        self::assertSame('$.foo[0]', $writer->getPath());
    }

    public function testPathEndObject()
    {
        $writer = new JsonEncodeWriter();
        $writer->beginObject();
        $writer->name('foo');
        $writer->writeInteger(1);
        $writer->endObject();
        self::assertSame('$', $writer->getPath());
    }

    public function testPathBeginArray()
    {
        $writer = new JsonEncodeWriter();
        $writer->beginArray();
        self::assertSame('$', $writer->getPath());
    }

    public function testPathArrayValue()
    {
        $writer = new JsonEncodeWriter();
        $writer->beginArray();
        $writer->writeString('foo');
        self::assertSame('$[0]', $writer->getPath());
    }

    public function testPathArraySecondValue()
    {
        $writer = new JsonEncodeWriter();
        $writer->beginArray();
        $writer->writeString('foo');
        $writer->writeString('bar');
        self::assertSame('$[1]', $writer->getPath());
    }

    public function testPathArraySerializeNull()
    {
        $writer = new JsonEncodeWriter();
        $writer->setSerializeNull(true);
        $writer->beginArray();
        $writer->writeInteger(1);
        $writer->writeNull();
        self::assertSame('$[1]', $writer->getPath());
    }

    public function testPathArrayNotSerializeNull()
    {
        $writer = new JsonEncodeWriter();
        $writer->setSerializeNull(false);
        $writer->beginArray();
        $writer->writeInteger(1);
        $writer->writeNull();
        self::assertSame('$[0]', $writer->getPath());
    }

    public function testPathArrayInArray()
    {
        $writer = new JsonEncodeWriter();
        $writer->beginArray();
        $writer->beginArray();
        self::assertSame('$[0]', $writer->getPath());
    }

    public function testPathArrayInArrayValue()
    {
        $writer = new JsonEncodeWriter();
        $writer->beginArray();
        $writer->beginArray();
        $writer->writeInteger(1);
        self::assertSame('$[0][0]', $writer->getPath());
    }

    public function testPathObjectInArray()
    {
        $writer = new JsonEncodeWriter();
        $writer->beginArray();
        $writer->beginObject();
        self::assertSame('$[0]', $writer->getPath());
    }

    public function testPathObjectInArrayValue()
    {
        $writer = new JsonEncodeWriter();
        $writer->beginArray();
        $writer->beginObject();
        $writer->name('foo');
        self::assertSame('$[0].foo', $writer->getPath());
    }

    public function testPathEndArray()
    {
        $writer = new JsonEncodeWriter();
        $writer->beginArray();
        $writer->writeString('foo');
        $writer->writeString('bar');
        $writer->endArray();
        self::assertSame('$', $writer->getPath());
    }

    public function testPathInteger()
    {
        $writer = new JsonEncodeWriter();
        $writer->writeInteger(1);
        self::assertSame('$', $writer->getPath());
    }

    public function testPathString()
    {
        $writer = new JsonEncodeWriter();
        $writer->writeString('foo');
        self::assertSame('$', $writer->getPath());
    }

    public function testPathFloat()
    {
        $writer = new JsonEncodeWriter();
        $writer->writeFloat(1.5);
        self::assertSame('$', $writer->getPath());
    }

    public function testPathBoolean()
    {
        $writer = new JsonEncodeWriter();
        $writer->writeBoolean(false);
        self::assertSame('$', $writer->getPath());
    }

    public function testComplexObject()
    {
        $writer = new JsonEncodeWriter();
        $writer->beginObject();
        $writer->name('name');
        $writer->beginObject();
        $writer->name('nested');
        $writer->writeInteger(2);
        $writer->name('array');
        $writer->beginArray();
        $writer->writeInteger(1);
        $writer->writeInteger(2);
        $writer->endArray();
        $writer->endObject();
        $writer->name('second');
        $writer->beginArray();
        $writer->writeInteger(1);
        $writer->beginObject();
        $writer->name('nested2');
        $writer->beginArray();
        $writer->writeInteger(1);
        $writer->writeInteger(2);

        self::assertSame('$.second[1].nested2[1]', $writer->getPath());
    }

    public function testComplexObjectFail()
    {
        $writer = new JsonEncodeWriter();
        $writer->beginObject();
        $writer->name('name');
        $writer->beginObject();
        $writer->name('nested');
        $writer->writeInteger(2);
        $writer->name('array');
        $writer->beginArray();
        $writer->writeInteger(1);
        $writer->writeInteger(2);
        $writer->endArray();
        $writer->endObject();
        $writer->name('second');
        $writer->beginArray();
        $writer->writeInteger(1);
        $writer->beginObject();
        $writer->name('nested2');
        $writer->beginArray();
        $writer->writeInteger(1);
        $writer->writeInteger(2);
        $writer->endArray();

        try {
            $writer->endArray();
        } catch (LogicException $exception) {
            self::assertSame('Cannot call endArray() if not serializing array at "$.second[1].nested2"', $exception->getMessage());
            return;
        }
        self::fail('Failed to throw exception');
    }

    public function testSerializeNullDefault()
    {
        $writer = new JsonEncodeWriter();

        self::assertFalse($writer->isSerializeNull());
    }

    public function testIsSerializeNull()
    {
        $writer = new JsonEncodeWriter();
        $writer->setSerializeNull(true);

        self::assertTrue($writer->isSerializeNull());
    }
}
