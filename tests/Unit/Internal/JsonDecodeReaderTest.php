<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal;

use ArrayIterator;
use PHPUnit_Framework_TestCase;
use SplStack;
use stdClass;
use Tebru\Gson\Exception\JsonParseException;
use Tebru\Gson\Exception\JsonSyntaxException;
use Tebru\Gson\Internal\JsonDecodeReader;
use Tebru\Gson\Internal\StdClassIterator;
use Tebru\Gson\JsonToken;

/**
 * Class JsonDecodeReaderTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\JsonDecodeReader
 * @covers \Tebru\Gson\Internal\JsonReader
 */
class JsonDecodeReaderTest extends PHPUnit_Framework_TestCase
{
    public function testMalformedJson()
    {
        try {
            new JsonDecodeReader('asdf');
        } catch (JsonParseException $exception) {
            self::assertSame('Could not decode json, the error message was: "Syntax error"', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testBeginArray()
    {
        $reader = new JsonDecodeReader('[1]');
        $reader->beginArray();

        $expected = new SplStack();
        $expected->push(new ArrayIterator([2]));

        $stack = $this->stack($reader);

        $top = array_pop($stack);
        self::assertInstanceOf(ArrayIterator::class, $top);
        self::assertSame(1, $top->current());
    }

    public function testBeginArrayInvalidToken()
    {
        $reader = new JsonDecodeReader('{}');
        try {
            $reader->beginArray();
        } catch (JsonSyntaxException $exception) {
            self::assertSame('Expected "begin-array", but found "begin-object" at "$"', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testEndArrayEmpty()
    {
        $reader = new JsonDecodeReader('[]');
        $reader->beginArray();
        $reader->endArray();

        self::assertAttributeCount(0, 'stack', $reader);
    }

    public function testEndArrayNonEmpty()
    {
        $reader = new JsonDecodeReader('[1]');
        $reader->beginArray();
        $reader->nextInteger();
        $reader->endArray();

        self::assertAttributeCount(0, 'stack', $reader);
    }

    public function testEndArrayInvalidToken()
    {
        $reader = new JsonDecodeReader('[{}]');
        $reader->beginArray();
        try {
            $reader->endArray();
        } catch (JsonSyntaxException $exception) {
            self::assertSame('Expected "end-array", but found "begin-object" at "$[0]"', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testBeginObject()
    {
        $reader = new JsonDecodeReader('{"key": "value"}');
        $reader->beginObject();

        $stack = $this->stack($reader);
        $top = array_pop($stack);

        self::assertInstanceOf(StdClassIterator::class, $top);
        self::assertSame('key', $top->key());
        self::assertSame('value', $top->current());
    }

    public function testBeginObjectInvalidToken()
    {
        $reader = new JsonDecodeReader('[]');
        try {
            $reader->beginObject();
        } catch (JsonSyntaxException $exception) {
            self::assertSame('Expected "begin-object", but found "begin-array" at "$"', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testEndObjectEmpty()
    {
        $reader = new JsonDecodeReader('{}');
        $reader->beginObject();
        $reader->endObject();

        self::assertAttributeCount(0, 'stack', $reader);
    }

    public function testEndObjectNonEmpty()
    {
        $reader = new JsonDecodeReader('{"test": 1}');
        $reader->beginObject();
        $reader->nextName();
        $reader->nextInteger();
        $reader->endObject();

        self::assertAttributeCount(0, 'stack', $reader);
    }

    public function testEndObjectInvalidToken()
    {
        $reader = new JsonDecodeReader('{"test": 1}');
        $reader->beginObject();
        $reader->nextName();
        try {
            $reader->endObject();
        } catch (JsonSyntaxException $exception) {
            self::assertSame('Expected "end-object", but found "number" at "$.test"', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testHasNextObjectTrue()
    {
        $reader = new JsonDecodeReader('{"test": 1}');
        $reader->beginObject();

        self::assertTrue($reader->hasNext());
    }

    public function testHasNextObjectFalse()
    {
        $reader = new JsonDecodeReader('{}');
        $reader->beginObject();

        self::assertFalse($reader->hasNext());
    }

    public function testHasNextArrayTrue()
    {
        $reader = new JsonDecodeReader('[1]');
        $reader->beginArray();

        self::assertTrue($reader->hasNext());
    }

    public function testHasNextArrayFalse()
    {
        $reader = new JsonDecodeReader('[]');
        $reader->beginArray();

        self::assertFalse($reader->hasNext());
    }

    public function testNextBooleanTrue()
    {
        $reader = new JsonDecodeReader('true');

        self::assertTrue($reader->nextBoolean());
    }

    public function testNextBooleanFalse()
    {
        $reader = new JsonDecodeReader('false');

        self::assertFalse($reader->nextBoolean());
    }

    public function testNextBooleanInvalidToken()
    {
        $reader = new JsonDecodeReader('[true, "tru"]');
        $reader->beginArray();
        $reader->nextBoolean();
        try {
            $reader->nextBoolean();
        } catch (JsonSyntaxException $exception) {
            self::assertSame('Expected "boolean", but found "string" at "$[1]"', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testNextDouble()
    {
        $reader = new JsonDecodeReader('1.1');

        self::assertSame(1.1, $reader->nextDouble());
    }

    public function testNextDoubleAsInt()
    {
        $reader = new JsonDecodeReader('1');

        self::assertSame(1.0, $reader->nextDouble());
    }

    public function testNextDoubleInvalidToken()
    {
        $reader = new JsonDecodeReader('{"foo": "1.1"}');
        $reader->beginObject();
        $reader->nextName();
        try {
            $reader->nextDouble();
        } catch (JsonSyntaxException $exception) {
            self::assertSame('Expected "number", but found "string" at "$.foo"', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testNextInteger()
    {
        $reader = new JsonDecodeReader('1');

        self::assertSame(1, $reader->nextInteger());
    }

    public function testNextIntegerInvalidToken()
    {
        $reader = new JsonDecodeReader('["1"]');
        $reader->beginArray();
        try {
            $reader->nextInteger();
        } catch (JsonSyntaxException $exception) {
            self::assertSame('Expected "number", but found "string" at "$[0]"', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testNextString()
    {
        $reader = new JsonDecodeReader('"test"');

        self::assertSame('test', $reader->nextString());
    }

    public function testNextStringIntType()
    {
        $reader = new JsonDecodeReader('"1"');

        self::assertSame('1', $reader->nextString());
    }

    public function testNextStringDoubleType()
    {
        $reader = new JsonDecodeReader('"1.1"');

        self::assertSame('1.1', $reader->nextString());
    }

    public function testNextStringBooleanTrueType()
    {
        $reader = new JsonDecodeReader('"true"');

        self::assertSame('true', $reader->nextString());
    }

    public function testNextStringBooleanFalseType()
    {
        $reader = new JsonDecodeReader('"false"');

        self::assertSame('false', $reader->nextString());
    }

    public function testNextStringNullType()
    {
        $reader = new JsonDecodeReader('"null"');

        self::assertSame('null', $reader->nextString());
    }

    public function testNextStringIgnoresDoubleQuote()
    {
        $string = 'te"st';
        $reader = new JsonDecodeReader(json_encode($string));

        self::assertSame('te"st', $reader->nextString());
    }

    public function testNextStringIgnoresOtherTerminationCharacters()
    {
        $reader = new JsonDecodeReader('"te]},st"');

        self::assertSame('te]},st', $reader->nextString());
    }

    public function testNextStringWithEscapedCharacters()
    {
        $string = 'te\\\/\b\f\n\r\t\u1234st';
        $reader = new JsonDecodeReader(json_encode($string));

        self::assertSame($string, $reader->nextString());
    }

    public function testNextStringWithEmoji()
    {
        $reader = new JsonDecodeReader('"te👍st"');

        self::assertSame('te👍st', $reader->nextString());
    }

    public function testNextStringInvalidToken()
    {
        $reader = new JsonDecodeReader('1');
        try {
            $reader->nextString();
        } catch (JsonSyntaxException $exception) {
            self::assertSame('Expected "string", but found "number" at "$"', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testNextStringName()
    {
        $reader = new JsonDecodeReader('{"key": "value"}');
        $reader->beginObject();

        self::assertSame('key', $reader->nextString());
    }

    public function testNextNull()
    {
        $reader = new JsonDecodeReader('null');

        self::assertNull($reader->nextNull());
    }

    public function testNextNullInvalidToken()
    {
        $reader = new JsonDecodeReader('"test"');
        try {
            $reader->nextNull();
        } catch (JsonSyntaxException $exception) {
            self::assertSame('Expected "null", but found "string" at "$"', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testNextName()
    {
        $reader = new JsonDecodeReader('{"test": 1}');
        $reader->beginObject();

        self::assertSame('test', $reader->nextName());
    }

    public function testNextNameInvalidToken()
    {
        $reader = new JsonDecodeReader('{"test": "test"}');
        $reader->beginObject();
        $reader->nextName();
        try {
            $reader->nextName();
        } catch (JsonSyntaxException $exception) {
            self::assertSame('Expected "name", but found "string" at "$.test"', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testPeekEmptyArrayEnding()
    {
        $reader = new JsonDecodeReader('[]');
        $reader->beginArray();

        self::assertEquals(JsonToken::END_ARRAY, $reader->peek());
    }

    public function testPeekEmptyArrayDefault()
    {
        $reader = new JsonDecodeReader('[1]');
        $reader->beginArray();

        self::assertEquals(JsonToken::NUMBER, $reader->peek());
    }

    public function testPeekNonEmptyArrayEnding()
    {
        $reader = new JsonDecodeReader('[1]');
        $reader->beginArray();
        $reader->nextInteger();

        self::assertEquals(JsonToken::END_ARRAY, $reader->peek());
    }

    public function testPeekNonEmptyArrayNext()
    {
        $reader = new JsonDecodeReader('[1, 2]');
        $reader->beginArray();
        $reader->nextInteger();

        self::assertEquals(JsonToken::NUMBER, $reader->peek());
    }

    public function testPeekNonEmptyObjectEnding()
    {
        $reader = new JsonDecodeReader('{"test": true}');
        $reader->beginObject();
        $reader->nextName();
        $reader->nextBoolean();

        self::assertEquals(JsonToken::END_OBJECT, $reader->peek());
    }

    public function testPeekNonEmptyObjectNext()
    {
        $reader = new JsonDecodeReader('{"test": true, "test2": false}');
        $reader->beginObject();
        $reader->nextName();
        $reader->nextBoolean();

        self::assertEquals(JsonToken::NAME, $reader->peek());
    }

    public function testPeekEmptyObjectEnding()
    {
        $reader = new JsonDecodeReader('{}');
        $reader->beginObject();

        self::assertEquals(JsonToken::END_OBJECT, $reader->peek());
    }

    public function testPeekEmptyObjectName()
    {
        $reader = new JsonDecodeReader('{"test": true}');
        $reader->beginObject();

        self::assertEquals(JsonToken::NAME, $reader->peek());
    }

    public function testPeekDanglingName()
    {
        $reader = new JsonDecodeReader('{"test": "test2"}');
        $reader->beginObject();
        $reader->nextName();

        self::assertEquals(JsonToken::STRING, $reader->peek());
    }

    public function testPeekEmptyDocumentBeginObject()
    {
        $reader = new JsonDecodeReader('{}');

        self::assertEquals(JsonToken::BEGIN_OBJECT, $reader->peek());
    }

    public function testPeekEmptyDocumentBeginArray()
    {
        $reader = new JsonDecodeReader('[]');

        self::assertEquals(JsonToken::BEGIN_ARRAY, $reader->peek());
    }

    public function testPeekEmptyDocument()
    {
        $reader = new JsonDecodeReader('[]');
        $reader->beginArray();
        $reader->endArray();

        self::assertEquals(JsonToken::END_DOCUMENT, $reader->peek());
    }

    public function testValueArray()
    {
        $reader = new JsonDecodeReader('[[]]');
        $reader->beginArray();

        self::assertEquals(JsonToken::BEGIN_ARRAY, $reader->peek());
    }

    public function testValueObject()
    {
        $reader = new JsonDecodeReader('[{}]');
        $reader->beginArray();

        self::assertEquals(JsonToken::BEGIN_OBJECT, $reader->peek());
    }

    public function testValueString()
    {
        $reader = new JsonDecodeReader('"test"');

        self::assertEquals(JsonToken::STRING, $reader->peek());
    }

    public function testValueTrue()
    {
        $reader = new JsonDecodeReader('true');

        self::assertEquals(JsonToken::BOOLEAN, $reader->peek());
    }

    public function testValueFalse()
    {
        $reader = new JsonDecodeReader('false');

        self::assertEquals(JsonToken::BOOLEAN, $reader->peek());
    }

    public function testValueNull()
    {
        $reader = new JsonDecodeReader('null');

        self::assertEquals(JsonToken::NULL, $reader->peek());
    }

    /**
     * @dataProvider provideValidNumbers
     */
    public function testValueNumber($number)
    {
        $reader = new JsonDecodeReader(sprintf('%s', $number));

        self::assertEquals(JsonToken::NUMBER, $reader->peek());
    }

    public function testSkipValue()
    {
        $array = [
            'skip' => [
                'prop1' => [
                    true,
                    false,
                    ['inner1' => 'innervalue'],
                ],
            ],
            'nextProp' => 1,
        ];
        $reader = new JsonDecodeReader(json_encode($array));
        $reader->beginObject();
        $reader->nextName();
        $reader->skipValue();

        self::assertSame('nextProp', $reader->nextName());
    }

    public function testFormattedJson()
    {
        $string = '{  
           "id": 1,
           "addresses":[  
              {  
                 "city": "Bloomington",
                 "state": "MN",
                 "zip": 55431
              }
           ],
           "active": true
        }';

        $reader = new JsonDecodeReader($string);
        $reader->beginObject();
        self::assertSame('id', $reader->nextName());
        self::assertSame(1, $reader->nextInteger());
        self::assertSame('addresses', $reader->nextName());
        $reader->beginArray();
        $reader->beginObject();
        self::assertSame('city', $reader->nextName());
        self::assertSame('Bloomington', $reader->nextString());
        self::assertSame('state', $reader->nextName());
        self::assertSame('MN', $reader->nextString());
        self::assertSame('zip', $reader->nextName());
        self::assertSame(55431, $reader->nextInteger());
        $reader->endObject();
        $reader->endArray();
        self::assertSame('active', $reader->nextName());
        self::assertTrue($reader->nextBoolean());
        $reader->endObject();
    }

    public function testGetPathSimpleName()
    {
        $reader = new JsonDecodeReader('{"name": 1}');
        $reader->beginObject();
        $reader->nextName();

        $path = $reader->getPath();

        self::assertSame('$.name', $path);
    }

    public function testGetPathNestedName()
    {
        $reader = new JsonDecodeReader('{"name": {"name2": 1}}');
        $reader->beginObject();
        $reader->nextName();
        $reader->beginObject();
        $reader->nextName();

        $path = $reader->getPath();

        self::assertSame('$.name.name2', $path);
    }

    public function testGetPathSimpleArray()
    {
        $reader = new JsonDecodeReader('[1, 2]');
        $reader->beginArray();
        $reader->nextInteger();

        $path = $reader->getPath();

        self::assertSame('$[1]', $path);
    }

    public function testGetPathNestedArray()
    {
        $reader = new JsonDecodeReader('[1, [1, 2]]');
        $reader->beginArray();
        $reader->nextInteger();
        $reader->beginArray();

        $path = $reader->getPath();

        self::assertSame('$[1][0]', $path);
    }

    public function testComplexObject()
    {
        $reader = new JsonDecodeReader('
            {
                "name": {
                    "nested": 2,
                    "array": [1,2]
                },
                "second": [
                    1,
                    {
                        "nested2": [1, 2, 3]
                    }
                ]
            }
        ');
        $reader->beginObject();
        $reader->nextName();
        $reader->beginObject();
        $reader->nextName();
        $reader->nextInteger();
        $reader->nextName();
        $reader->beginArray();
        $reader->nextInteger();
        $reader->nextInteger();
        $reader->endArray();
        $reader->endObject();
        $reader->nextName();
        $reader->beginArray();
        $reader->nextInteger();
        $reader->beginObject();
        $reader->nextName();
        $reader->beginArray();
        $reader->nextInteger();
        $reader->nextInteger();

        $path = $reader->getPath();

        self::assertSame('$.second[1].nested2[2]', $path);
    }

    public function testGetPathBegin()
    {
        $reader = new JsonDecodeReader('{"name": 1}');

        $path = $reader->getPath();

        self::assertSame('$', $path);
    }

    public function testGetPathBeginObject()
    {
        $reader = new JsonDecodeReader('{"name": 1}');
        $reader->beginObject();

        $path = $reader->getPath();

        self::assertSame('$', $path);
    }

    public function testGetPathLastValueInObject()
    {
        $reader = new JsonDecodeReader('{"name": 1}');
        $reader->beginObject();
        $reader->nextName();
        $reader->nextInteger();

        $path = $reader->getPath();

        self::assertSame('$.name', $path);
    }

    public function testGetPathEndObject()
    {
        $reader = new JsonDecodeReader('{"name": 1}');
        $reader->beginObject();
        $reader->nextName();
        $reader->nextInteger();
        $reader->endObject();

        $path = $reader->getPath();

        self::assertSame('$', $path);
    }

    public function testGetPathBeginArray()
    {
        $reader = new JsonDecodeReader('[]');
        $reader->beginArray();

        $path = $reader->getPath();

        self::assertSame('$[0]', $path);
    }

    public function testGetPathFirstArray()
    {
        $reader = new JsonDecodeReader('[1, 2]');
        $reader->beginArray();
        $reader->nextInteger();

        $path = $reader->getPath();

        self::assertSame('$[1]', $path);
    }

    public function testGetPathLastArray()
    {
        $reader = new JsonDecodeReader('[1, 2]');
        $reader->beginArray();
        $reader->nextInteger();
        $reader->nextInteger();

        $path = $reader->getPath();

        self::assertSame('$[2]', $path);
    }

    public function testGetPathEndArray()
    {
        $reader = new JsonDecodeReader('[1, 2]');
        $reader->beginArray();
        $reader->nextInteger();
        $reader->nextInteger();
        $reader->endArray();

        $path = $reader->getPath();

        self::assertSame('$', $path);
    }

    public function testGetPayload()
    {
        $reader = new JsonDecodeReader('{"name": 1}');

        $payload = $reader->getPayload();
        $expected = new stdClass();
        $expected->name = 1;

        self::assertEquals($expected, $payload);
    }

    public function provideValidNumbers()
    {
        return [[0], [1], [2], [3], [4], [5], [6], [7], [8], [9], [-1]];
    }

    private function stack(JsonDecodeReader $reader): array
    {
        return self::readAttribute($reader, 'stack');
    }
}
