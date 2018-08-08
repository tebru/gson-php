<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal;

use PHPUnit_Framework_TestCase;
use stdClass;
use Tebru\Gson\Exception\JsonDecodeException;
use Tebru\Gson\Exception\JsonParseException;
use Tebru\Gson\Exception\JsonSyntaxException;
use Tebru\Gson\Internal\JsonDecodeReader;
use Tebru\Gson\Internal\DefaultReaderContext;
use Tebru\Gson\JsonToken;

/**
 * Class JsonDecodeReaderTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\JsonDecodeReader
 * @covers \Tebru\Gson\Internal\JsonReader
 * @covers \Tebru\Gson\Internal\DefaultReaderContext
 * @covers \Tebru\Gson\Exception\JsonDecodeException
 */
class JsonDecodeReaderTest extends PHPUnit_Framework_TestCase
{
    public function testMalformedJson()
    {
        try {
            new JsonDecodeReader('asdf', new DefaultReaderContext());
        } catch (JsonDecodeException $exception) {
            self::assertSame('Could not decode json, the error message was: "Syntax error"', $exception->getMessage());
            self::assertSame('asdf', $exception->getPayload());
            self::assertSame(JSON_ERROR_SYNTAX, $exception->getCode());
            return;
        }
        self::assertTrue(false);
    }

    public function testBeginArray()
    {
        $reader = new JsonDecodeReader('[1]', new DefaultReaderContext());
        $reader->beginArray();

        $stack = $this->stack($reader);

        self::assertSame([null, null, 1], $stack);
    }

    public function testBeginArrayInvalidToken()
    {
        $reader = new JsonDecodeReader('{}', new DefaultReaderContext());
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
        $reader = new JsonDecodeReader('[]', new DefaultReaderContext());
        $reader->beginArray();
        $reader->endArray();

        self::assertAttributeSame(1, 'stackSize', $reader);
    }

    public function testEndArrayNonEmpty()
    {
        $reader = new JsonDecodeReader('[1]', new DefaultReaderContext());
        $reader->beginArray();
        $reader->nextInteger();
        $reader->endArray();

        self::assertAttributeSame(1, 'stackSize', $reader);
    }

    public function testEndArrayInvalidToken()
    {
        $reader = new JsonDecodeReader('[{}]', new DefaultReaderContext());
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
        $reader = new JsonDecodeReader('{"key": "value"}', new DefaultReaderContext());
        $reader->beginObject();

        $stack = $this->stack($reader);

        self::assertSame([null, null, 'value', 'key'], $stack);
    }

    public function testBeginObjectInvalidToken()
    {
        $reader = new JsonDecodeReader('[]', new DefaultReaderContext());
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
        $reader = new JsonDecodeReader('{}', new DefaultReaderContext());
        $reader->beginObject();
        $reader->endObject();

        self::assertAttributeSame(1, 'stackSize', $reader);
    }

    public function testEndObjectNonEmpty()
    {
        $reader = new JsonDecodeReader('{"test": 1}', new DefaultReaderContext());
        $reader->beginObject();
        $reader->nextName();
        $reader->nextInteger();
        $reader->endObject();

        self::assertAttributeSame(1, 'stackSize', $reader);
    }

    public function testEndObjectInvalidToken()
    {
        $reader = new JsonDecodeReader('{"test": 1}', new DefaultReaderContext());
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
        $reader = new JsonDecodeReader('{"test": 1}', new DefaultReaderContext());
        $reader->beginObject();

        self::assertTrue($reader->hasNext());
    }

    public function testHasNextObjectFalse()
    {
        $reader = new JsonDecodeReader('{}', new DefaultReaderContext());
        $reader->beginObject();

        self::assertFalse($reader->hasNext());
    }

    public function testHasNextArrayTrue()
    {
        $reader = new JsonDecodeReader('[1]', new DefaultReaderContext());
        $reader->beginArray();

        self::assertTrue($reader->hasNext());
    }

    public function testHasNextArrayFalse()
    {
        $reader = new JsonDecodeReader('[]', new DefaultReaderContext());
        $reader->beginArray();

        self::assertFalse($reader->hasNext());
    }

    public function testNextBooleanTrue()
    {
        $reader = new JsonDecodeReader('true', new DefaultReaderContext());

        self::assertTrue($reader->nextBoolean());
    }

    public function testNextBooleanFalse()
    {
        $reader = new JsonDecodeReader('false', new DefaultReaderContext());

        self::assertFalse($reader->nextBoolean());
    }

    public function testNextBooleanInvalidToken()
    {
        $reader = new JsonDecodeReader('[true, "tru"]', new DefaultReaderContext());
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
        $reader = new JsonDecodeReader('1.1', new DefaultReaderContext());

        self::assertSame(1.1, $reader->nextDouble());
    }

    public function testNextDoubleAsInt()
    {
        $reader = new JsonDecodeReader('1', new DefaultReaderContext());

        self::assertSame(1.0, $reader->nextDouble());
    }

    public function testNextDoubleInvalidToken()
    {
        $reader = new JsonDecodeReader('{"foo": "1.1"}', new DefaultReaderContext());
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
        $reader = new JsonDecodeReader('1', new DefaultReaderContext());

        self::assertSame(1, $reader->nextInteger());
    }

    public function testNextIntegerInvalidToken()
    {
        $reader = new JsonDecodeReader('["1"]', new DefaultReaderContext());
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
        $reader = new JsonDecodeReader('"test"', new DefaultReaderContext());

        self::assertSame('test', $reader->nextString());
    }

    public function testNextStringIntType()
    {
        $reader = new JsonDecodeReader('"1"', new DefaultReaderContext());

        self::assertSame('1', $reader->nextString());
    }

    public function testNextStringDoubleType()
    {
        $reader = new JsonDecodeReader('"1.1"', new DefaultReaderContext());

        self::assertSame('1.1', $reader->nextString());
    }

    public function testNextStringBooleanTrueType()
    {
        $reader = new JsonDecodeReader('"true"', new DefaultReaderContext());

        self::assertSame('true', $reader->nextString());
    }

    public function testNextStringBooleanFalseType()
    {
        $reader = new JsonDecodeReader('"false"', new DefaultReaderContext());

        self::assertSame('false', $reader->nextString());
    }

    public function testNextStringNullType()
    {
        $reader = new JsonDecodeReader('"null"', new DefaultReaderContext());

        self::assertSame('null', $reader->nextString());
    }

    public function testNextStringIgnoresDoubleQuote()
    {
        $string = 'te"st';
        $reader = new JsonDecodeReader(json_encode($string), new DefaultReaderContext());

        self::assertSame('te"st', $reader->nextString());
    }

    public function testNextStringIgnoresOtherTerminationCharacters()
    {
        $reader = new JsonDecodeReader('"te]},st"', new DefaultReaderContext());

        self::assertSame('te]},st', $reader->nextString());
    }

    public function testNextStringWithEscapedCharacters()
    {
        $string = 'te\\\/\b\f\n\r\t\u1234st';
        $reader = new JsonDecodeReader(json_encode($string), new DefaultReaderContext());

        self::assertSame($string, $reader->nextString());
    }

    public function testNextStringWithEmoji()
    {
        $reader = new JsonDecodeReader('"te👍st"', new DefaultReaderContext());

        self::assertSame('te👍st', $reader->nextString());
    }

    public function testNextStringInvalidToken()
    {
        $reader = new JsonDecodeReader('1', new DefaultReaderContext());
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
        $reader = new JsonDecodeReader('{"key": "value"}', new DefaultReaderContext());
        $reader->beginObject();

        self::assertSame('key', $reader->nextString());
    }

    public function testNextNull()
    {
        $reader = new JsonDecodeReader('null', new DefaultReaderContext());

        self::assertNull($reader->nextNull());
    }

    public function testNextNullInvalidToken()
    {
        $reader = new JsonDecodeReader('"test"', new DefaultReaderContext());
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
        $reader = new JsonDecodeReader('{"test": 1}', new DefaultReaderContext());
        $reader->beginObject();

        self::assertSame('test', $reader->nextName());
    }

    public function testNextNameInvalidToken()
    {
        $reader = new JsonDecodeReader('{"test": "test"}', new DefaultReaderContext());
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
        $reader = new JsonDecodeReader('[]', new DefaultReaderContext());
        $reader->beginArray();

        self::assertEquals(JsonToken::END_ARRAY, $reader->peek());
    }

    public function testPeekEmptyArrayDefault()
    {
        $reader = new JsonDecodeReader('[1]', new DefaultReaderContext());
        $reader->beginArray();

        self::assertEquals(JsonToken::NUMBER, $reader->peek());
    }

    public function testPeekNonEmptyArrayEnding()
    {
        $reader = new JsonDecodeReader('[1]', new DefaultReaderContext());
        $reader->beginArray();
        $reader->nextInteger();

        self::assertEquals(JsonToken::END_ARRAY, $reader->peek());
    }

    public function testPeekNonEmptyArrayNext()
    {
        $reader = new JsonDecodeReader('[1, 2]', new DefaultReaderContext());
        $reader->beginArray();
        $reader->nextInteger();

        self::assertEquals(JsonToken::NUMBER, $reader->peek());
    }

    public function testPeekNonEmptyObjectEnding()
    {
        $reader = new JsonDecodeReader('{"test": true}', new DefaultReaderContext());
        $reader->beginObject();
        $reader->nextName();
        $reader->nextBoolean();

        self::assertEquals(JsonToken::END_OBJECT, $reader->peek());
    }

    public function testPeekNonEmptyObjectNext()
    {
        $reader = new JsonDecodeReader('{"test": true, "test2": false}', new DefaultReaderContext());
        $reader->beginObject();
        $reader->nextName();
        $reader->nextBoolean();

        self::assertEquals(JsonToken::NAME, $reader->peek());
    }

    public function testPeekEmptyObjectEnding()
    {
        $reader = new JsonDecodeReader('{}', new DefaultReaderContext());
        $reader->beginObject();

        self::assertEquals(JsonToken::END_OBJECT, $reader->peek());
    }

    public function testPeekEmptyObjectName()
    {
        $reader = new JsonDecodeReader('{"test": true}', new DefaultReaderContext());
        $reader->beginObject();

        self::assertEquals(JsonToken::NAME, $reader->peek());
    }

    public function testPeekDanglingName()
    {
        $reader = new JsonDecodeReader('{"test": "test2"}', new DefaultReaderContext());
        $reader->beginObject();
        $reader->nextName();

        self::assertEquals(JsonToken::STRING, $reader->peek());
    }

    public function testPeekEmptyDocumentBeginObject()
    {
        $reader = new JsonDecodeReader('{}', new DefaultReaderContext());

        self::assertEquals(JsonToken::BEGIN_OBJECT, $reader->peek());
    }

    public function testPeekEmptyDocumentBeginArray()
    {
        $reader = new JsonDecodeReader('[]', new DefaultReaderContext());

        self::assertEquals(JsonToken::BEGIN_ARRAY, $reader->peek());
    }

    public function testPeekEmptyDocument()
    {
        $reader = new JsonDecodeReader('[]', new DefaultReaderContext());
        $reader->beginArray();
        $reader->endArray();

        self::assertEquals(JsonToken::END_DOCUMENT, $reader->peek());
    }

    public function testValueArray()
    {
        $reader = new JsonDecodeReader('[[]]', new DefaultReaderContext());
        $reader->beginArray();

        self::assertEquals(JsonToken::BEGIN_ARRAY, $reader->peek());
    }

    public function testValueObject()
    {
        $reader = new JsonDecodeReader('[{}]', new DefaultReaderContext());
        $reader->beginArray();

        self::assertEquals(JsonToken::BEGIN_OBJECT, $reader->peek());
    }

    public function testValueString()
    {
        $reader = new JsonDecodeReader('"test"', new DefaultReaderContext());

        self::assertEquals(JsonToken::STRING, $reader->peek());
    }

    public function testValueTrue()
    {
        $reader = new JsonDecodeReader('true', new DefaultReaderContext());

        self::assertEquals(JsonToken::BOOLEAN, $reader->peek());
    }

    public function testValueFalse()
    {
        $reader = new JsonDecodeReader('false', new DefaultReaderContext());

        self::assertEquals(JsonToken::BOOLEAN, $reader->peek());
    }

    public function testValueNull()
    {
        $reader = new JsonDecodeReader('null', new DefaultReaderContext());

        self::assertEquals(JsonToken::NULL, $reader->peek());
    }

    /**
     * @dataProvider provideValidNumbers
     */
    public function testValueNumber($number)
    {
        $reader = new JsonDecodeReader(sprintf('%s', $number), new DefaultReaderContext());

        self::assertEquals(JsonToken::NUMBER, $reader->peek());
    }

    public function testSkipValueObject()
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
        $reader = new JsonDecodeReader(json_encode($array), new DefaultReaderContext());
        $reader->beginObject();
        $reader->nextName();
        $reader->skipValue();

        self::assertSame('nextProp', $reader->nextName());
    }

    public function testSkipValueArray()
    {
        $array = [
            'skip' => [1, 2, 3],
            'nextProp' => 1,
        ];
        $reader = new JsonDecodeReader(json_encode($array), new DefaultReaderContext());
        $reader->beginObject();
        $reader->nextName();
        $reader->skipValue();

        self::assertSame('nextProp', $reader->nextName());
    }

    public function testSkipValueScalar()
    {
        $array = [
            'skip' => 1,
            'nextProp' => 1,
        ];
        $reader = new JsonDecodeReader(json_encode($array), new DefaultReaderContext());
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

        $reader = new JsonDecodeReader($string, new DefaultReaderContext());
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
        $reader = new JsonDecodeReader('{"name": 1}', new DefaultReaderContext());
        $reader->beginObject();
        $reader->nextName();

        $path = $reader->getPath();

        self::assertSame('$.name', $path);
    }

    public function testGetPathNestedName()
    {
        $reader = new JsonDecodeReader('{"name": {"name2": 1}}', new DefaultReaderContext());
        $reader->beginObject();
        $reader->nextName();
        $reader->beginObject();
        $reader->nextName();

        $path = $reader->getPath();

        self::assertSame('$.name.name2', $path);
    }

    public function testGetPathSimpleArray()
    {
        $reader = new JsonDecodeReader('[1, 2]', new DefaultReaderContext());
        $reader->beginArray();
        $reader->nextInteger();

        $path = $reader->getPath();

        self::assertSame('$[0]', $path);
    }

    public function testGetPathNestedArray()
    {
        $reader = new JsonDecodeReader('[1, [1, 2]]', new DefaultReaderContext());
        $reader->beginArray();
        $reader->nextInteger();
        $reader->beginArray();
        $reader->nextInteger();

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
        ', new DefaultReaderContext());
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

        self::assertSame('$.second[1].nested2[1]', $path);
    }

    public function testGetPathBegin()
    {
        $reader = new JsonDecodeReader('{"name": 1}', new DefaultReaderContext());

        $path = $reader->getPath();

        self::assertSame('$', $path);
    }

    public function testGetPathBeginObject()
    {
        $reader = new JsonDecodeReader('{"name": 1}', new DefaultReaderContext());
        $reader->beginObject();

        $path = $reader->getPath();

        self::assertSame('$', $path);
    }

    public function testGetPathLastValueInObject()
    {
        $reader = new JsonDecodeReader('{"name": 1}', new DefaultReaderContext());
        $reader->beginObject();
        $reader->nextName();
        $reader->nextInteger();

        $path = $reader->getPath();

        self::assertSame('$.name', $path);
    }

    public function testGetPathEndObject()
    {
        $reader = new JsonDecodeReader('{"name": 1}', new DefaultReaderContext());
        $reader->beginObject();
        $reader->nextName();
        $reader->nextInteger();
        $reader->endObject();

        $path = $reader->getPath();

        self::assertSame('$', $path);
    }

    public function testGetPathBeginArray()
    {
        $reader = new JsonDecodeReader('[]', new DefaultReaderContext());
        $reader->beginArray();

        $path = $reader->getPath();

        self::assertSame('$', $path);
    }

    public function testGetPathFirstArray()
    {
        $reader = new JsonDecodeReader('[1, 2]', new DefaultReaderContext());
        $reader->beginArray();
        $reader->nextInteger();

        $path = $reader->getPath();

        self::assertSame('$[0]', $path);
    }

    public function testGetPathLastArray()
    {
        $reader = new JsonDecodeReader('[1, 2]', new DefaultReaderContext());
        $reader->beginArray();
        $reader->nextInteger();
        $reader->nextInteger();

        $path = $reader->getPath();

        self::assertSame('$[1]', $path);
    }

    public function testGetPathEndArray()
    {
        $reader = new JsonDecodeReader('[1, 2]', new DefaultReaderContext());
        $reader->beginArray();
        $reader->nextInteger();
        $reader->nextInteger();
        $reader->endArray();

        $path = $reader->getPath();

        self::assertSame('$', $path);
    }

    public function testGetPayload()
    {
        $reader = new JsonDecodeReader('{"name": 1}', new DefaultReaderContext());

        $payload = $reader->getPayload();
        $expected = new stdClass();
        $expected->name = 1;

        self::assertEquals($expected, $payload);
    }

    public function testGetContext()
    {
        $context = new DefaultReaderContext();
        $context->setUsesExistingObject(true);
        $reader = new JsonDecodeReader('{"name": 1}', $context);

        self::assertTrue($reader->getContext()->usesExistingObject());
    }

    public function testGetContextDefaults()
    {
        $reader = new JsonDecodeReader('{"name": 1}', new DefaultReaderContext());

        self::assertFalse($reader->getContext()->usesExistingObject());
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
