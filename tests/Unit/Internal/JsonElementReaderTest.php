<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal;

use PHPUnit\Framework\TestCase;
use Tebru\Gson\Element\JsonArray;
use Tebru\Gson\Element\JsonNull;
use Tebru\Gson\Element\JsonObject;
use Tebru\Gson\Element\JsonPrimitive;
use Tebru\Gson\Exception\JsonSyntaxException;
use Tebru\Gson\Internal\DefaultReaderContext;
use Tebru\Gson\Internal\JsonElementReader;
use Tebru\Gson\Internal\TypeAdapter\JsonElementTypeAdapter;
use Tebru\Gson\JsonToken;

/**
 * Class JsonElementReaderTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\JsonElementReader
 * @covers \Tebru\Gson\Internal\JsonReader
 */
class JsonElementReaderTest extends TestCase
{
    public function testBeginArray(): void
    {
        $jsonArray = new JsonArray();
        $jsonArray->addInteger(1);
        $reader = new JsonElementReader($jsonArray, new DefaultReaderContext());
        $reader->beginArray();

        $stack = $this->stack($reader);

        self::assertEquals([null, null, JsonPrimitive::create(1)], $stack);
    }

    public function testBeginArrayInvalidToken(): void
    {
        $reader = new JsonElementReader(new JsonObject(), new DefaultReaderContext());
        try {
            $reader->beginArray();
        } catch (JsonSyntaxException $exception) {
            self::assertSame('Expected "begin-array", but found "begin-object" at "$"', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testEndArrayEmpty(): void
    {
        $reader = new JsonElementReader(new JsonArray(), new DefaultReaderContext());
        $reader->beginArray();
        $reader->endArray();

        self::assertAttributeSame(1, 'stackSize', $reader);
    }

    public function testEndArrayNonEmpty(): void
    {
        $jsonArray = new JsonArray();
        $jsonArray->addInteger(1);
        $reader = new JsonElementReader($jsonArray, new DefaultReaderContext());
        $reader->beginArray();
        $reader->nextInteger();
        $reader->endArray();

        self::assertAttributeSame(1, 'stackSize', $reader);
    }

    public function testEndArrayInvalidToken(): void
    {
        $jsonArray = new JsonArray();
        $jsonArray->addJsonElement(new JsonObject());
        $reader = new JsonElementReader($jsonArray, new DefaultReaderContext());
        $reader->beginArray();
        try {
            $reader->endArray();
        } catch (JsonSyntaxException $exception) {
            self::assertSame('Expected "end-array", but found "begin-object" at "$[0]"', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testBeginObject(): void
    {
        $jsonObject = new JsonObject();
        $jsonObject->addString('key', 'value');
        $reader = new JsonElementReader($jsonObject, new DefaultReaderContext());
        $reader->beginObject();

        $stack = $this->stack($reader);

        self::assertEquals([null, null, JsonPrimitive::create('value'), 'key'], $stack);
    }

    public function testBeginObjectInvalidToken(): void
    {
        $reader = new JsonElementReader(new JsonArray(), new DefaultReaderContext());
        try {
            $reader->beginObject();
        } catch (JsonSyntaxException $exception) {
            self::assertSame('Expected "begin-object", but found "begin-array" at "$"', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testEndObjectEmpty(): void
    {
        $reader = new JsonElementReader(new JsonObject(), new DefaultReaderContext());
        $reader->beginObject();
        $reader->endObject();

        self::assertAttributeSame(1, 'stackSize', $reader);
    }

    public function testEndObjectNonEmpty(): void
    {
        $jsonObject = new JsonObject();
        $jsonObject->addString('key', 'value');
        $reader = new JsonElementReader($jsonObject, new DefaultReaderContext());
        $reader->beginObject();
        $reader->nextName();
        $reader->nextString();
        $reader->endObject();

        self::assertAttributeSame(1, 'stackSize', $reader);
    }

    public function testEndObjectInvalidToken(): void
    {
        $jsonObject = new JsonObject();
        $jsonObject->addString('key', 'value');
        $reader = new JsonElementReader($jsonObject, new DefaultReaderContext());
        $reader->beginObject();
        try {
            $reader->endObject();
        } catch (JsonSyntaxException $exception) {
            self::assertSame('Expected "end-object", but found "name" at "$"', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testHasNextObjectTrue(): void
    {
        $jsonObject = new JsonObject();
        $jsonObject->addString('key', 'value');
        $reader = new JsonElementReader($jsonObject, new DefaultReaderContext());
        $reader->beginObject();

        self::assertTrue($reader->hasNext());
    }

    public function testHasNextObjectFalse(): void
    {
        $reader = new JsonElementReader(new JsonObject(), new DefaultReaderContext());
        $reader->beginObject();

        self::assertFalse($reader->hasNext());
    }

    public function testHasNextArrayTrue(): void
    {
        $jsonArray = new JsonArray();
        $jsonArray->addInteger(1);
        $reader = new JsonElementReader($jsonArray, new DefaultReaderContext());
        $reader->beginArray();

        self::assertTrue($reader->hasNext());
    }

    public function testHasNextArrayFalse(): void
    {
        $reader = new JsonElementReader(new JsonArray(), new DefaultReaderContext());
        $reader->beginArray();

        self::assertFalse($reader->hasNext());
    }

    public function testNextBooleanTrue(): void
    {
        $reader = new JsonElementReader(JsonPrimitive::create(true), new DefaultReaderContext());

        self::assertTrue($reader->nextBoolean());
    }

    public function testNextBooleanFalse(): void
    {
        $reader = new JsonElementReader(JsonPrimitive::create(false), new DefaultReaderContext());

        self::assertFalse($reader->nextBoolean());
    }

    public function testNextBooleanInvalidToken(): void
    {
        $reader = new JsonElementReader(JsonPrimitive::create('test'), new DefaultReaderContext());
        try {
            $reader->nextBoolean();
        } catch (JsonSyntaxException $exception) {
            self::assertSame('Expected "boolean", but found "string" at "$"', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testNextDouble(): void
    {
        $reader = new JsonElementReader(JsonPrimitive::create(1.1), new DefaultReaderContext());

        self::assertSame(1.1, $reader->nextDouble());
    }

    public function testNextDoubleAsInt(): void
    {
        $reader = new JsonElementReader(JsonPrimitive::create(1), new DefaultReaderContext());

        self::assertSame(1.0, $reader->nextDouble());
    }

    public function testNextDoubleInvalidToken(): void
    {
        $reader = new JsonElementReader(JsonPrimitive::create('1.1'), new DefaultReaderContext());
        try {
            $reader->nextDouble();
        } catch (JsonSyntaxException $exception) {
            self::assertSame('Expected "number", but found "string" at "$"', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testNextInteger(): void
    {
        $reader = new JsonElementReader(JsonPrimitive::create(1), new DefaultReaderContext());

        self::assertSame(1, $reader->nextInteger());
    }

    public function testNextIntegerInvalidToken(): void
    {
        $reader = new JsonElementReader(JsonPrimitive::create('1'), new DefaultReaderContext());
        try {
            $reader->nextInteger();
        } catch (JsonSyntaxException $exception) {
            self::assertSame('Expected "number", but found "string" at "$"', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testNextString(): void
    {
        $reader = new JsonElementReader(JsonPrimitive::create('test'), new DefaultReaderContext());

        self::assertSame('test', $reader->nextString());
    }

    public function testNextStringIntType(): void
    {
        $reader = new JsonElementReader(JsonPrimitive::create('1'), new DefaultReaderContext());

        self::assertSame('1', $reader->nextString());
    }

    public function testNextStringDoubleType(): void
    {
        $reader = new JsonElementReader(JsonPrimitive::create('1.1'), new DefaultReaderContext());

        self::assertSame('1.1', $reader->nextString());
    }

    public function testNextStringBooleanTrueType(): void
    {
        $reader = new JsonElementReader(JsonPrimitive::create('true'), new DefaultReaderContext());

        self::assertSame('true', $reader->nextString());
    }

    public function testNextStringBooleanFalseType(): void
    {
        $reader = new JsonElementReader(JsonPrimitive::create('false'), new DefaultReaderContext());

        self::assertSame('false', $reader->nextString());
    }

    public function testNextStringNullType(): void
    {
        $reader = new JsonElementReader(JsonPrimitive::create('null'), new DefaultReaderContext());

        self::assertSame('null', $reader->nextString());
    }

    public function testNextStringIgnoresDoubleQuote(): void
    {
        $string = 'te"st';
        $reader = new JsonElementReader(JsonPrimitive::create($string), new DefaultReaderContext());

        self::assertSame('te"st', $reader->nextString());
    }

    public function testNextStringIgnoresOtherTerminationCharacters(): void
    {
        $reader = new JsonElementReader(JsonPrimitive::create('te]},st'), new DefaultReaderContext());

        self::assertSame('te]},st', $reader->nextString());
    }

    public function testNextStringWithEscapedCharacters(): void
    {
        $string = 'te\\\/\b\f\n\r\t\u1234st';
        $reader = new JsonElementReader(JsonPrimitive::create($string), new DefaultReaderContext());

        self::assertSame($string, $reader->nextString());
    }

    public function testNextStringWithEmoji(): void
    {
        $reader = new JsonElementReader(JsonPrimitive::create('te👍st'), new DefaultReaderContext());

        self::assertSame('te👍st', $reader->nextString());
    }

    public function testNextStringInvalidToken(): void
    {
        $reader = new JsonElementReader(JsonPrimitive::create(1), new DefaultReaderContext());
        try {
            $reader->nextString();
        } catch (JsonSyntaxException $exception) {
            self::assertSame('Expected "string", but found "number" at "$"', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testNextStringName(): void
    {
        $jsonObject = new JsonObject();
        $jsonObject->addString('key', 'value');
        $reader = new JsonElementReader($jsonObject, new DefaultReaderContext());
        $reader->beginObject();

        self::assertSame('key', $reader->nextString());
    }

    public function testNextNull(): void
    {
        $reader = new JsonElementReader(new JsonNull(), new DefaultReaderContext());

        self::assertNull($reader->nextNull());
    }

    public function testNextNullInvalidToken(): void
    {
        $reader = new JsonElementReader(JsonPrimitive::create('test'), new DefaultReaderContext());
        try {
            $reader->nextNull();
        } catch (JsonSyntaxException $exception) {
            self::assertSame('Expected "null", but found "string" at "$"', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testNextName(): void
    {
        $jsonObject = new JsonObject();
        $jsonObject->addString('key', 'value');
        $reader = new JsonElementReader($jsonObject, new DefaultReaderContext());
        $reader->beginObject();

        self::assertSame('key', $reader->nextName());
    }

    public function testNextNameInvalidToken(): void
    {
        $jsonObject = new JsonObject();
        $jsonObject->addString('key', 'value');
        $reader = new JsonElementReader($jsonObject, new DefaultReaderContext());
        $reader->beginObject();
        $reader->nextName();
        try {
            $reader->nextName();
        } catch (JsonSyntaxException $exception) {
            self::assertSame('Expected "name", but found "string" at "$.key"', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testPeekEmptyArrayEnding(): void
    {
        $reader = new JsonElementReader(new JsonArray(), new DefaultReaderContext());
        $reader->beginArray();

        self::assertEquals(JsonToken::END_ARRAY, $reader->peek());
    }

    public function testPeekEmptyArrayDefault(): void
    {
        $jsonArray = new JsonArray();
        $jsonArray->addInteger(1);
        $reader = new JsonElementReader($jsonArray, new DefaultReaderContext());
        $reader->beginArray();

        self::assertEquals(JsonToken::NUMBER, $reader->peek());
    }

    public function testPeekNonEmptyArrayEnding(): void
    {
        $jsonArray = new JsonArray();
        $jsonArray->addInteger(1);
        $reader = new JsonElementReader($jsonArray, new DefaultReaderContext());
        $reader->beginArray();
        $reader->nextInteger();

        self::assertEquals(JsonToken::END_ARRAY, $reader->peek());
    }

    public function testPeekNonEmptyArrayNext(): void
    {
        $jsonArray = new JsonArray();
        $jsonArray->addInteger(1);
        $jsonArray->addInteger(2);
        $reader = new JsonElementReader($jsonArray, new DefaultReaderContext());
        $reader->beginArray();
        $reader->nextInteger();

        self::assertEquals(JsonToken::NUMBER, $reader->peek());
    }

    public function testPeekNonEmptyObjectEnding(): void
    {
        $jsonObject = new JsonObject();
        $jsonObject->addBoolean('key', true);
        $reader = new JsonElementReader($jsonObject, new DefaultReaderContext());
        $reader->beginObject();
        $reader->nextName();
        $reader->nextBoolean();

        self::assertEquals(JsonToken::END_OBJECT, $reader->peek());
    }

    public function testPeekNonEmptyObjectNext(): void
    {
        $jsonObject = new JsonObject();
        $jsonObject->addBoolean('key', true);
        $jsonObject->addString('key2', false);
        $reader = new JsonElementReader($jsonObject, new DefaultReaderContext());
        $reader->beginObject();
        $reader->nextName();
        $reader->nextBoolean();

        self::assertEquals(JsonToken::NAME, $reader->peek());
    }

    public function testPeekEmptyObjectEnding(): void
    {
        $reader = new JsonElementReader(new JsonObject(), new DefaultReaderContext());
        $reader->beginObject();

        self::assertEquals(JsonToken::END_OBJECT, $reader->peek());
    }

    public function testPeekEmptyObjectName(): void
    {
        $jsonObject = new JsonObject();
        $jsonObject->addBoolean('key', true);
        $reader = new JsonElementReader($jsonObject, new DefaultReaderContext());
        $reader->beginObject();

        self::assertEquals(JsonToken::NAME, $reader->peek());
    }

    public function testPeekDanglingName(): void
    {
        $jsonObject = new JsonObject();
        $jsonObject->addBoolean('key', true);
        $reader = new JsonElementReader($jsonObject, new DefaultReaderContext());
        $reader->beginObject();
        $reader->nextName();

        self::assertEquals(JsonToken::BOOLEAN, $reader->peek());
    }

    public function testPeekEmptyDocumentBeginObject(): void
    {
        $reader = new JsonElementReader(new JsonObject(), new DefaultReaderContext());

        self::assertEquals(JsonToken::BEGIN_OBJECT, $reader->peek());
    }

    public function testPeekEmptyDocumentBeginArray(): void
    {
        $reader = new JsonElementReader(new JsonArray(), new DefaultReaderContext());

        self::assertEquals(JsonToken::BEGIN_ARRAY, $reader->peek());
    }

    public function testPeekEmptyDocument(): void
    {
        $reader = new JsonElementReader(new JsonArray(), new DefaultReaderContext());
        $reader->beginArray();
        $reader->endArray();

        self::assertEquals(JsonToken::END_DOCUMENT, $reader->peek());
    }

    public function testValueArray(): void
    {
        $jsonArray = new JsonArray();
        $jsonArray->addJsonElement(new JsonArray());
        $reader = new JsonElementReader($jsonArray, new DefaultReaderContext());
        $reader->beginArray();

        self::assertEquals(JsonToken::BEGIN_ARRAY, $reader->peek());
    }

    public function testValueObject(): void
    {
        $jsonArray = new JsonArray();
        $jsonArray->addJsonElement(new JsonObject());
        $reader = new JsonElementReader($jsonArray, new DefaultReaderContext());
        $reader->beginArray();

        self::assertEquals(JsonToken::BEGIN_OBJECT, $reader->peek());
    }

    public function testValueString(): void
    {
        $reader = new JsonElementReader(JsonPrimitive::create('test'), new DefaultReaderContext());

        self::assertEquals(JsonToken::STRING, $reader->peek());
    }

    public function testValueTrue(): void
    {
        $reader = new JsonElementReader(JsonPrimitive::create(true), new DefaultReaderContext());

        self::assertEquals(JsonToken::BOOLEAN, $reader->peek());
    }

    public function testValueFalse(): void
    {
        $reader = new JsonElementReader(JsonPrimitive::create(false), new DefaultReaderContext());

        self::assertEquals(JsonToken::BOOLEAN, $reader->peek());
    }

    public function testValueNull(): void
    {
        $reader = new JsonElementReader(new JsonNull(), new DefaultReaderContext());

        self::assertEquals(JsonToken::NULL, $reader->peek());
    }

    /**
     * @dataProvider provideValidNumbers
     */
    public function testValueNumber($number): void
    {
        $reader = new JsonElementReader(JsonPrimitive::create((int)sprintf('%d', $number)), new DefaultReaderContext());

        self::assertEquals(JsonToken::NUMBER, $reader->peek());
    }

    public function testSkipValue(): void
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
        $adapter = new JsonElementTypeAdapter();
        $jsonObject = $adapter->readFromJson(json_encode($array));

        $reader = new JsonElementReader($jsonObject, new DefaultReaderContext());
        $reader->beginObject();
        $reader->nextName();
        $reader->skipValue();

        self::assertSame('nextProp', $reader->nextName());
    }

    public function testFormattedJson(): void
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

        $adapter = new JsonElementTypeAdapter();
        $jsonObject = $adapter->readFromJson($string);

        $reader = new JsonElementReader($jsonObject, new DefaultReaderContext());
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

    public function testGetPayload(): void
    {
        $adapter = new JsonElementTypeAdapter();
        $jsonObject = $adapter->readFromJson('{"name": 1}');
        $reader = new JsonElementReader($jsonObject, new DefaultReaderContext());

        $payload = $reader->getPayload();

        self::assertSame($jsonObject, $payload);
    }

    public function provideValidNumbers(): array
    {
        return [[0], [1], [2], [3], [4], [5], [6], [7], [8], [9], [-1]];
    }

    private function stack(JsonElementReader $reader): array
    {
        return self::readAttribute($reader, 'stack');
    }
}
