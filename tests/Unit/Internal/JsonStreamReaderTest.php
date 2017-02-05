<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal;

use PHPUnit_Framework_TestCase;
use Tebru\Gson\Exception\MalformedJsonException;
use Tebru\Gson\Exception\UnexpectedJsonScopeException;
use Tebru\Gson\Exception\UnexpectedJsonTokenException;
use Tebru\Gson\Exception\UnexpectedJsonTypeException;
use Tebru\Gson\Internal\JsonStreamReader;
use Tebru\Gson\Internal\JsonScope;
use Tebru\Gson\JsonToken;
use function GuzzleHttp\Psr7\stream_for;

/**
 * Class JsonStreamReaderTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\JsonToken
 * @covers \Tebru\Gson\Internal\JsonStreamReader
 * @covers \Tebru\Gson\Internal\JsonScope
 */
class JsonStreamReaderTest extends PHPUnit_Framework_TestCase
{
    public function testBeginArray()
    {
        $reader = new JsonStreamReader(stream_for('['));
        $reader->beginArray();

        self::assertAttributeEquals([JsonScope::NONEMPTY_DOCUMENT, JsonScope::EMPTY_ARRAY], 'stack', $reader);
    }

    public function testBeginArrayInvalidToken()
    {
        $this->expectException(UnexpectedJsonTokenException::class);
        $this->expectExceptionMessage('Expected "[", but found "{"');

        $reader = new JsonStreamReader(stream_for('{'));
        $reader->beginArray();
    }

    public function testEndArrayEmpty()
    {
        $reader = new JsonStreamReader(stream_for('[]'));
        $reader->beginArray();
        $reader->endArray();

        self::assertAttributeEquals([JsonScope::NONEMPTY_DOCUMENT], 'stack', $reader);
    }

    public function testEndArrayNonEmpty()
    {
        $reader = new JsonStreamReader(stream_for('[1]'));
        $reader->beginArray();
        $reader->nextInteger();
        $reader->endArray();

        self::assertAttributeEquals([JsonScope::NONEMPTY_DOCUMENT], 'stack', $reader);
    }

    public function testEndArrayInvalidToken()
    {
        $this->expectException(UnexpectedJsonTokenException::class);
        $this->expectExceptionMessage('Expected "]", but found "{"');

        $reader = new JsonStreamReader(stream_for('[{}]'));
        $reader->beginArray();
        $reader->endArray();
    }

    public function testBeginObject()
    {
        $reader = new JsonStreamReader(stream_for('{'));
        $reader->beginObject();

        self::assertAttributeEquals([JsonScope::NONEMPTY_DOCUMENT, JsonScope::EMPTY_OBJECT], 'stack', $reader);
    }

    public function testBeginObjectInvalidToken()
    {
        $this->expectException(UnexpectedJsonTokenException::class);
        $this->expectExceptionMessage('Expected "{", but found "["');

        $reader = new JsonStreamReader(stream_for('['));
        $reader->beginObject();
    }

    public function testEndObjectEmpty()
    {
        $reader = new JsonStreamReader(stream_for('{}'));
        $reader->beginObject();
        $reader->endObject();

        self::assertAttributeEquals([JsonScope::NONEMPTY_DOCUMENT], 'stack', $reader);
    }

    public function testEndObjectNonEmpty()
    {
        $reader = new JsonStreamReader(stream_for('{"test": 1}'));
        $reader->beginObject();
        $reader->nextName();
        $reader->nextInteger();
        $reader->endObject();

        self::assertAttributeEquals([JsonScope::NONEMPTY_DOCUMENT], 'stack', $reader);
    }

    public function testEndObjectInvalidToken()
    {
        $this->expectException(UnexpectedJsonTokenException::class);
        $this->expectExceptionMessage('Expected "}", but found """');

        $reader = new JsonStreamReader(stream_for('{"test": 1}'));
        $reader->beginObject();
        $reader->endObject();
    }

    public function testHasNextObjectTrue()
    {
        $reader = new JsonStreamReader(stream_for('{"test": 1}'));
        $reader->beginObject();

        self::assertTrue($reader->hasNext());
    }

    public function testHasNextObjectFalse()
    {
        $reader = new JsonStreamReader(stream_for('{}'));
        $reader->beginObject();

        self::assertFalse($reader->hasNext());
    }

    public function testHasNextArrayTrue()
    {
        $reader = new JsonStreamReader(stream_for('[1]'));
        $reader->beginArray();

        self::assertTrue($reader->hasNext());
    }

    public function testHasNextArrayFalse()
    {
        $reader = new JsonStreamReader(stream_for('[]'));
        $reader->beginArray();

        self::assertFalse($reader->hasNext());
    }

    public function testNextBooleanTrue()
    {
        $reader = new JsonStreamReader(stream_for('true'));

        self::assertTrue($reader->nextBoolean());
    }

    public function testNextBooleanFalse()
    {
        $reader = new JsonStreamReader(stream_for('false'));

        self::assertFalse($reader->nextBoolean());
    }

    public function testNextBooleanInvalidToken()
    {
        $this->expectException(UnexpectedJsonTokenException::class);
        $this->expectExceptionMessage('Expected boolean, but got "string"');

        $reader = new JsonStreamReader(stream_for('"tru"'));
        $reader->nextBoolean();
    }

    public function testNextBooleanInvalidType()
    {
        $this->expectException(UnexpectedJsonTypeException::class);
        $this->expectExceptionMessage('Expected boolean, but got "string"');

        $reader = new JsonStreamReader(stream_for('trues'));
        $reader->nextBoolean();
    }

    public function testNextDouble()
    {
        $reader = new JsonStreamReader(stream_for('1.1'));

        self::assertSame(1.1, $reader->nextDouble());
    }

    public function testNextDoubleAsInt()
    {
        $reader = new JsonStreamReader(stream_for('1'));

        self::assertSame(1.0, $reader->nextDouble());
    }

    public function testNextDoubleInvalidToken()
    {
        $this->expectException(UnexpectedJsonTokenException::class);
        $this->expectExceptionMessage('Expected double, but got "string"');

        $reader = new JsonStreamReader(stream_for('"1.1"'));
        $reader->nextDouble();
    }

    public function testNextDoubleInvalidType()
    {
        $this->expectException(UnexpectedJsonTypeException::class);
        $this->expectExceptionMessage('Expected double, but got "string"');

        $reader = new JsonStreamReader(stream_for('1asdf'));
        $reader->nextDouble();
    }

    public function testNextInteger()
    {
        $reader = new JsonStreamReader(stream_for('1'));

        self::assertSame(1, $reader->nextInteger());
    }

    public function testNextIntegerInvalidToken()
    {
        $this->expectException(UnexpectedJsonTokenException::class);
        $this->expectExceptionMessage('Expected integer, but got "string"');

        $reader = new JsonStreamReader(stream_for('"1"'));
        $reader->nextInteger();
    }

    public function testNextIntegerInvalidType()
    {
        $this->expectException(UnexpectedJsonTypeException::class);
        $this->expectExceptionMessage('Expected integer, but got "string"');

        $reader = new JsonStreamReader(stream_for('1asdf'));
        $reader->nextInteger();
    }

    public function testNextString()
    {
        $reader = new JsonStreamReader(stream_for('"test"'));

        self::assertSame('test', $reader->nextString());
    }

    public function testNextStringIntType()
    {
        $reader = new JsonStreamReader(stream_for('"1"'));

        self::assertSame('1', $reader->nextString());
    }

    public function testNextStringDoubleType()
    {
        $reader = new JsonStreamReader(stream_for('"1.1"'));

        self::assertSame('1.1', $reader->nextString());
    }

    public function testNextStringBooleanTrueType()
    {
        $reader = new JsonStreamReader(stream_for('"true"'));

        self::assertSame('true', $reader->nextString());
    }

    public function testNextStringBooleanFalseType()
    {
        $reader = new JsonStreamReader(stream_for('"false"'));

        self::assertSame('false', $reader->nextString());
    }

    public function testNextStringNullType()
    {
        $reader = new JsonStreamReader(stream_for('"null"'));

        self::assertSame('null', $reader->nextString());
    }

    public function testNextStringIgnoresDoubleQuote()
    {
        $string = 'te"st';
        $reader = new JsonStreamReader(stream_for(json_encode($string)));

        self::assertSame('te\"st', $reader->nextString());
    }

    public function testNextStringIgnoresOtherTerminationCharacters()
    {
        $reader = new JsonStreamReader(stream_for('"te]},st"'));

        self::assertSame('te]},st', $reader->nextString());
    }

    public function testNextStringWithEscapedCharacters()
    {
        $reader = new JsonStreamReader(stream_for('"te\\\/\b\f\n\r\t\u1234st"'));

        self::assertSame('te\\\/\b\f\n\r\t\u1234st', $reader->nextString());
    }

    public function testNextStringWithEmoji()
    {
        $reader = new JsonStreamReader(stream_for('"te👍st"'));

        self::assertSame('te👍st', $reader->nextString());
    }

    public function testNextStringInvalidToken()
    {
        $this->expectException(UnexpectedJsonTokenException::class);
        $this->expectExceptionMessage('Expected string, but got "number"');

        $reader = new JsonStreamReader(stream_for('1'));
        $reader->nextString();
    }

    public function testNextNull()
    {
        $reader = new JsonStreamReader(stream_for('null'));

        self::assertNull($reader->nextNull());
    }

    public function testNextNullInvalidToken()
    {
        $this->expectException(UnexpectedJsonTokenException::class);
        $this->expectExceptionMessage('Expected null, but got "string"');

        $reader = new JsonStreamReader(stream_for('"test"'));
        $reader->nextNull();
    }

    public function testNextNullInvalidType()
    {
        $this->expectException(UnexpectedJsonTypeException::class);
        $this->expectExceptionMessage('Expected null, but got "string"');

        $reader = new JsonStreamReader(stream_for('nulls'));
        $reader->nextNull();
    }

    public function testNextName()
    {
        $reader = new JsonStreamReader(stream_for('{"test"'));
        $reader->beginObject();

        self::assertSame('test', $reader->nextName());
    }

    public function testNextNameInvalidScope()
    {
        $this->expectException(UnexpectedJsonScopeException::class);
        $this->expectExceptionMessage('Method call not allowed in current scope');

        $reader = new JsonStreamReader(stream_for('{"test": "test"}'));
        $reader->beginObject();
        $reader->nextName();
        $reader->nextName();
    }

    public function testPeekEmptyArrayEnding()
    {
        $reader = new JsonStreamReader(stream_for('[]'));
        $reader->beginArray();

        self::assertEquals(JsonToken::END_ARRAY, $reader->peek());
    }

    public function testPeekEmptyArrayDefault()
    {
        $reader = new JsonStreamReader(stream_for('[1]'));
        $reader->beginArray();

        self::assertEquals(JsonToken::NUMBER, $reader->peek());
    }

    public function testPeekNonEmptyArrayEnding()
    {
        $reader = new JsonStreamReader(stream_for('[1]'));
        $reader->beginArray();
        $reader->nextInteger();

        self::assertEquals(JsonToken::END_ARRAY, $reader->peek());
    }

    public function testPeekNonEmptyArrayNext()
    {
        $reader = new JsonStreamReader(stream_for('[1, 2]'));
        $reader->beginArray();
        $reader->nextInteger();

        self::assertEquals(JsonToken::NUMBER, $reader->peek());
    }

    public function testPeekNonEmptyArrayException()
    {
        $this->expectException(MalformedJsonException::class);
        $this->expectExceptionMessage('Expected "]" or ",", but found "}"');

        $reader = new JsonStreamReader(stream_for('[1}]'));
        $reader->beginArray();
        $reader->nextInteger();
        $reader->peek();
    }

    public function testPeekNonEmptyObjectEnding()
    {
        $reader = new JsonStreamReader(stream_for('{"test": true}'));
        $reader->beginObject();
        $reader->nextName();
        $reader->nextBoolean();

        self::assertEquals(JsonToken::END_OBJECT, $reader->peek());
    }

    public function testPeekNonEmptyObjectNext()
    {
        $reader = new JsonStreamReader(stream_for('{"test": true, "test2": false}'));
        $reader->beginObject();
        $reader->nextName();
        $reader->nextBoolean();

        self::assertEquals(JsonToken::NAME, $reader->peek());
    }

    public function testPeekNonEmptyObjectNextException()
    {
        $this->expectException(MalformedJsonException::class);
        $this->expectExceptionMessage('Expected ", but found ","');

        $reader = new JsonStreamReader(stream_for('{"test": true,,'));
        $reader->beginObject();
        $reader->nextName();
        $reader->nextBoolean();
        $reader->peek();
    }

    public function testPeekNonEmptyObjectException()
    {
        $this->expectException(MalformedJsonException::class);
        $this->expectExceptionMessage('Expected "}" or ",", but found "]"');

        $reader = new JsonStreamReader(stream_for('{"test": true]'));
        $reader->beginObject();
        $reader->nextName();
        $reader->nextBoolean();
        $reader->peek();
    }

    public function testPeekEmptyObjectEnding()
    {
        $reader = new JsonStreamReader(stream_for('{}'));
        $reader->beginObject();

        self::assertEquals(JsonToken::END_OBJECT, $reader->peek());
    }

    public function testPeekEmptyObjectName()
    {
        $reader = new JsonStreamReader(stream_for('{"test": true}'));
        $reader->beginObject();

        self::assertEquals(JsonToken::NAME, $reader->peek());
    }

    public function testPeekEmptyObjectException()
    {
        $this->expectException(MalformedJsonException::class);
        $this->expectExceptionMessage('Expected " or "}", but found "."');

        $reader = new JsonStreamReader(stream_for('{.'));
        $reader->beginObject();
        $reader->peek();
    }

    public function testPeekDanglingName()
    {
        $reader = new JsonStreamReader(stream_for('{"test": "test2"}'));
        $reader->beginObject();
        $reader->nextName();

        self::assertEquals(JsonToken::STRING, $reader->peek());
    }

    public function testPeekDanglingNameException()
    {
        $this->expectException(MalformedJsonException::class);
        $this->expectExceptionMessage('Expected ":", but found "."');

        $reader = new JsonStreamReader(stream_for('{"test".'));
        $reader->beginObject();
        $reader->nextName();
        $reader->peek();
    }

    public function testPeekEmptyDocumentBeginObject()
    {
        $reader = new JsonStreamReader(stream_for('{'));

        self::assertEquals(JsonToken::BEGIN_OBJECT, $reader->peek());
    }

    public function testPeekEmptyDocumentBeginArray()
    {
        $reader = new JsonStreamReader(stream_for('['));

        self::assertEquals(JsonToken::BEGIN_ARRAY, $reader->peek());
    }

    public function testValueArray()
    {
        $reader = new JsonStreamReader(stream_for('[['));
        $reader->beginArray();

        self::assertEquals(JsonToken::BEGIN_ARRAY, $reader->peek());
    }

    public function testValueObject()
    {
        $reader = new JsonStreamReader(stream_for('[{'));
        $reader->beginArray();

        self::assertEquals(JsonToken::BEGIN_OBJECT, $reader->peek());
    }

    public function testValueString()
    {
        $reader = new JsonStreamReader(stream_for('"'));

        self::assertEquals(JsonToken::STRING, $reader->peek());
    }

    public function testValueTrue()
    {
        $reader = new JsonStreamReader(stream_for('true'));

        self::assertEquals(JsonToken::BOOLEAN, $reader->peek());
    }

    public function testValueTrueCapital()
    {
        $reader = new JsonStreamReader(stream_for('TRUE'));

        self::assertEquals(JsonToken::BOOLEAN, $reader->peek());
    }

    public function testValueTrueException()
    {
        $this->expectException(MalformedJsonException::class);
        $this->expectExceptionMessage('Expected "true", but found "t1"');

        $reader = new JsonStreamReader(stream_for('t1'));
        $reader->peek();
    }

    public function testValueFalse()
    {
        $reader = new JsonStreamReader(stream_for('false'));

        self::assertEquals(JsonToken::BOOLEAN, $reader->peek());
    }

    public function testValueFalseCapital()
    {
        $reader = new JsonStreamReader(stream_for('FALSE'));

        self::assertEquals(JsonToken::BOOLEAN, $reader->peek());
    }

    public function testValueFalseException()
    {
        $this->expectException(MalformedJsonException::class);
        $this->expectExceptionMessage('Expected "false", but found "f1"');

        $reader = new JsonStreamReader(stream_for('f1'));
        $reader->peek();
    }

    public function testValueNull()
    {
        $reader = new JsonStreamReader(stream_for('null'));

        self::assertEquals(JsonToken::NULL, $reader->peek());
    }

    public function testValueNullCapital()
    {
        $reader = new JsonStreamReader(stream_for('NULL'));

        self::assertEquals(JsonToken::NULL, $reader->peek());
    }

    public function testValueNullException()
    {
        $this->expectException(MalformedJsonException::class);
        $this->expectExceptionMessage('Expected "null", but found "n1"');

        $reader = new JsonStreamReader(stream_for('n1'));
        $reader->peek();
    }

    /**
     * @dataProvider provideValidNumbers
     */
    public function testValueNumber($number)
    {
        $reader = new JsonStreamReader(stream_for(sprintf('%s', $number)));

        self::assertEquals(JsonToken::NUMBER, $reader->peek());
    }

    public function testValueException()
    {
        $this->expectException(MalformedJsonException::class);
        $this->expectExceptionMessage('Unable to handle "s" character');

        $reader = new JsonStreamReader(stream_for('s'));
        $reader->peek();
    }

    public function testWillUseCachedToken()
    {
        $reader = new JsonStreamReader(stream_for('{'));
        $reader->peek();

        self::assertEquals(JsonToken::BEGIN_OBJECT, $reader->peek());
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
        $reader = new JsonStreamReader(stream_for(json_encode($array)));
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

        $reader = new JsonStreamReader(stream_for($string));
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
}
