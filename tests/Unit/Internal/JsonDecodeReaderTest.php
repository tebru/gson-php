<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal;

use ArrayIterator;
use PHPUnit_Framework_TestCase;
use SplStack;
use Tebru\Gson\Exception\UnexpectedJsonTokenException;
use Tebru\Gson\Internal\JsonDecodeReader;
use Tebru\Gson\Internal\StdClassIterator;
use Tebru\Gson\JsonToken;

/**
 * Class JsonDecodeReaderTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\JsonDecodeReader
 */
class JsonDecodeReaderTest extends PHPUnit_Framework_TestCase
{
    public function testBeginArray()
    {
        $reader = new JsonDecodeReader('[1]');
        $reader->beginArray();

        $expected = new SplStack();
        $expected->push(new ArrayIterator([2]));

        $stack = $this->stack($reader);

        self::assertInstanceOf(ArrayIterator::class, $stack->top());
        self::assertSame(1, $stack->top()->current());
    }

    public function testBeginArrayInvalidToken()
    {
        $this->expectException(UnexpectedJsonTokenException::class);
        $this->expectExceptionMessage('Expected "begin-array", but found "begin-object"');

        $reader = new JsonDecodeReader('{}');
        $reader->beginArray();
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
        $this->expectException(UnexpectedJsonTokenException::class);
        $this->expectExceptionMessage('Expected "end-array", but found "begin-object"');

        $reader = new JsonDecodeReader('[{}]');
        $reader->beginArray();
        $reader->endArray();
    }

    public function testBeginObject()
    {
        $reader = new JsonDecodeReader('{"key": "value"}');
        $reader->beginObject();

        $stack = $this->stack($reader);

        self::assertInstanceOf(StdClassIterator::class, $stack->top());
        self::assertSame(['key', 'value'], $stack->top()->current());
    }

    public function testBeginObjectInvalidToken()
    {
        $this->expectException(UnexpectedJsonTokenException::class);
        $this->expectExceptionMessage('Expected "begin-object", but found "begin-array"');

        $reader = new JsonDecodeReader('[]');
        $reader->beginObject();
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
        $this->expectException(UnexpectedJsonTokenException::class);
        $this->expectExceptionMessage('Expected "end-object", but found "name"');

        $reader = new JsonDecodeReader('{"test": 1}');
        $reader->beginObject();
        $reader->endObject();
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
        $this->expectException(UnexpectedJsonTokenException::class);
        $this->expectExceptionMessage('Expected "boolean", but found "string"');

        $reader = new JsonDecodeReader('"tru"');
        $reader->nextBoolean();
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
        $this->expectException(UnexpectedJsonTokenException::class);
        $this->expectExceptionMessage('Expected "number", but found "string"');

        $reader = new JsonDecodeReader('"1.1"');
        $reader->nextDouble();
    }

    public function testNextInteger()
    {
        $reader = new JsonDecodeReader('1');

        self::assertSame(1, $reader->nextInteger());
    }

    public function testNextIntegerInvalidToken()
    {
        $this->expectException(UnexpectedJsonTokenException::class);
        $this->expectExceptionMessage('Expected "number", but found "string"');

        $reader = new JsonDecodeReader('"1"');
        $reader->nextInteger();
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
        $this->expectException(UnexpectedJsonTokenException::class);
        $this->expectExceptionMessage('Expected "string", but found "number"');

        $reader = new JsonDecodeReader('1');
        $reader->nextString();
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
        $this->expectException(UnexpectedJsonTokenException::class);
        $this->expectExceptionMessage('Expected "null", but found "string"');

        $reader = new JsonDecodeReader('"test"');
        $reader->nextNull();
    }

    public function testNextName()
    {
        $reader = new JsonDecodeReader('{"test": 1}');
        $reader->beginObject();

        self::assertSame('test', $reader->nextName());
    }

    public function testNextNameInvalidToken()
    {
        $this->expectException(UnexpectedJsonTokenException::class);
        $this->expectExceptionMessage('Expected "name", but found "string"');

        $reader = new JsonDecodeReader('{"test": "test"}');
        $reader->beginObject();
        $reader->nextName();
        $reader->nextName();
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

    public function provideValidNumbers()
    {
        return [[0], [1], [2], [3], [4], [5], [6], [7], [8], [9], [-1]];
    }

    private function stack(JsonDecodeReader $reader): SplStack
    {
        return self::readAttribute($reader, 'stack');
    }
}
