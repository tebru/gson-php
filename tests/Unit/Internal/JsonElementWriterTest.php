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
 * @covers \Tebru\Gson\Internal\JsonWriter
 * @covers \Tebru\Gson\Internal\JsonElementWriter
 * @covers \Tebru\Gson\Internal\JsonPath
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
            self::assertSame('Cannot call beginArray() before name() during object serialization at "$"', $exception->getMessage());
            return;
        }
        self::fail('Failed to throw exception');
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
            self::assertSame('Cannot call endArray() if not serializing array at "$"', $exception->getMessage());
            return;
        }
        self::fail('Failed to throw exception');
    }

    public function testEndArrayFirst()
    {
        $writer = new JsonElementWriter();
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
            self::assertSame('Cannot call beginObject() before name() during object serialization at "$"', $exception->getMessage());
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
            self::assertSame('Cannot call endObject() if not serializing object at "$"', $exception->getMessage());
            return;
        }
        self::fail('Failed to throw exception');
    }

    public function testEndObjectFirst()
    {
        $writer = new JsonElementWriter();
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
            self::assertSame('Cannot call name() at this point.  Either name() has already been called or object serialization has not been started at "$.foo"', $exception->getMessage());
            return;
        }
        self::fail('Failed to throw exception');
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
            self::assertSame('Cannot call writeInteger() before name() during object serialization at "$"', $exception->getMessage());
            return;
        }
        self::fail('Failed to throw exception');
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
            self::assertSame('Cannot call writeFloat() before name() during object serialization at "$"', $exception->getMessage());
            return;
        }
        self::fail('Failed to throw exception');
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
            self::assertSame('Cannot call writeString() before name() during object serialization at "$"', $exception->getMessage());
            return;
        }
        self::fail('Failed to throw exception');
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
            self::assertSame('Cannot call writeBoolean() before name() during object serialization at "$"', $exception->getMessage());
            return;
        }
        self::fail('Failed to throw exception');
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
            self::assertSame('Cannot call writeNull() before name() during object serialization at "$"', $exception->getMessage());
            return;
        }
        self::fail('Failed to throw exception');
    }

    public function testWriteTwoScalars()
    {
        $writer = new JsonElementWriter();
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
        $writer = new JsonElementWriter();
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

    public function testCanGetJsonElement()
    {
        $writer = new JsonElementWriter();
        $writer->beginObject();
        $writer->endObject();

        self::assertEquals(new JsonObject(), $writer->toJsonElement());
    }

    public function testPathBeginObject()
    {
        $writer = new JsonElementWriter();
        $writer->beginObject();
        self::assertSame('$', $writer->getPath());
    }

    public function testPathName()
    {
        $writer = new JsonElementWriter();
        $writer->beginObject();
        $writer->name('foo');
        self::assertSame('$.foo', $writer->getPath());
    }

    public function testPathObjectValue()
    {
        $writer = new JsonElementWriter();
        $writer->beginObject();
        $writer->name('foo');
        $writer->writeInteger(1);
        self::assertSame('$.foo', $writer->getPath());
    }

    public function testPathObjectSecondValue()
    {
        $writer = new JsonElementWriter();
        $writer->beginObject();
        $writer->name('foo');
        $writer->writeInteger(1);
        $writer->name('bar');
        $writer->writeBoolean(true);
        self::assertSame('$.bar', $writer->getPath());
    }

    public function testPathObjectSerializeNull()
    {
        $writer = new JsonElementWriter();
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
        $writer = new JsonElementWriter();
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
        $writer = new JsonElementWriter();
        $writer->beginObject();
        $writer->name('foo');
        $writer->beginObject();
        self::assertSame('$.foo', $writer->getPath());
    }

    public function testPathObjectInObjectValue()
    {
        $writer = new JsonElementWriter();
        $writer->beginObject();
        $writer->name('foo');
        $writer->beginObject();
        $writer->name('bar');
        self::assertSame('$.foo.bar', $writer->getPath());
    }

    public function testPathArrayInObject()
    {
        $writer = new JsonElementWriter();
        $writer->beginObject();
        $writer->name('foo');
        $writer->beginArray();
        self::assertSame('$.foo', $writer->getPath());
    }

    public function testPathArrayInObjectValue()
    {
        $writer = new JsonElementWriter();
        $writer->beginObject();
        $writer->name('foo');
        $writer->beginArray();
        $writer->writeInteger(1);
        self::assertSame('$.foo[0]', $writer->getPath());
    }

    public function testPathEndObject()
    {
        $writer = new JsonElementWriter();
        $writer->beginObject();
        $writer->name('foo');
        $writer->writeInteger(1);
        $writer->endObject();
        self::assertSame('$', $writer->getPath());
    }

    public function testPathBeginArray()
    {
        $writer = new JsonElementWriter();
        $writer->beginArray();
        self::assertSame('$', $writer->getPath());
    }

    public function testPathArrayValue()
    {
        $writer = new JsonElementWriter();
        $writer->beginArray();
        $writer->writeString('foo');
        self::assertSame('$[0]', $writer->getPath());
    }

    public function testPathArraySecondValue()
    {
        $writer = new JsonElementWriter();
        $writer->beginArray();
        $writer->writeString('foo');
        $writer->writeString('bar');
        self::assertSame('$[1]', $writer->getPath());
    }

    public function testPathArraySerializeNull()
    {
        $writer = new JsonElementWriter();
        $writer->setSerializeNull(true);
        $writer->beginArray();
        $writer->writeInteger(1);
        $writer->writeNull();
        self::assertSame('$[1]', $writer->getPath());
    }

    public function testPathArrayNotSerializeNull()
    {
        $writer = new JsonElementWriter();
        $writer->setSerializeNull(false);
        $writer->beginArray();
        $writer->writeInteger(1);
        $writer->writeNull();
        self::assertSame('$[0]', $writer->getPath());
    }

    public function testPathArrayInArray()
    {
        $writer = new JsonElementWriter();
        $writer->beginArray();
        $writer->beginArray();
        self::assertSame('$[0]', $writer->getPath());
    }

    public function testPathArrayInArrayValue()
    {
        $writer = new JsonElementWriter();
        $writer->beginArray();
        $writer->beginArray();
        $writer->writeInteger(1);
        self::assertSame('$[0][0]', $writer->getPath());
    }

    public function testPathObjectInArray()
    {
        $writer = new JsonElementWriter();
        $writer->beginArray();
        $writer->beginObject();
        self::assertSame('$[0]', $writer->getPath());
    }

    public function testPathObjectInArrayValue()
    {
        $writer = new JsonElementWriter();
        $writer->beginArray();
        $writer->beginObject();
        $writer->name('foo');
        self::assertSame('$[0].foo', $writer->getPath());
    }

    public function testPathEndArray()
    {
        $writer = new JsonElementWriter();
        $writer->beginArray();
        $writer->writeString('foo');
        $writer->writeString('bar');
        $writer->endArray();
        self::assertSame('$', $writer->getPath());
    }

    public function testPathInteger()
    {
        $writer = new JsonElementWriter();
        $writer->writeInteger(1);
        self::assertSame('$', $writer->getPath());
    }

    public function testPathString()
    {
        $writer = new JsonElementWriter();
        $writer->writeString('foo');
        self::assertSame('$', $writer->getPath());
    }

    public function testPathFloat()
    {
        $writer = new JsonElementWriter();
        $writer->writeFloat(1.5);
        self::assertSame('$', $writer->getPath());
    }

    public function testPathBoolean()
    {
        $writer = new JsonElementWriter();
        $writer->writeBoolean(false);
        self::assertSame('$', $writer->getPath());
    }

    public function testComplexObject()
    {
        $writer = new JsonElementWriter();
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
        $writer = new JsonElementWriter();
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
}
