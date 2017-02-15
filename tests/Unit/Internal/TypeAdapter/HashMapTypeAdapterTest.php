<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter;

use LogicException;
use PHPUnit_Framework_TestCase;
use Tebru\Collection\ArrayList;
use Tebru\Collection\HashMap;
use Tebru\Gson\Internal\PhpType;
use Tebru\Gson\Internal\TypeAdapter\Factory\ArrayListTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\ArrayTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\FloatTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\HashMapTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\IntegerTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\StringTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\WildcardTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\HashMapTypeAdapter;
use Tebru\Gson\Internal\TypeAdapterProvider;

/**
 * Class HashMapTypeAdapterTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\TypeAdapter\HashMapTypeAdapter
 */
class HashMapTypeAdapterTest extends PHPUnit_Framework_TestCase
{
    public function testDeserializeNull()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new HashMapTypeAdapterFactory(),
        ]);

        $adapter = $typeAdapterProvider->getAdapter(new PhpType('Map'));

        $result = $adapter->readFromJson('null');

        self::assertNull($result);
    }

    public function testDeserializeSimpleObject()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new StringTypeAdapterFactory(),
            new HashMapTypeAdapterFactory(),
            new WildcardTypeAdapterFactory(),
        ]);

        /** @var HashMapTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new PhpType('Map'));

        /** @var HashMap $result */
        $result = $adapter->readFromJson('{"key": "value"}');

        self::assertInstanceOf(HashMap::class, $result);
        self::assertSame('value', $result->get('key'));
    }

    public function testDeserializeObjectMultipleKeys()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new StringTypeAdapterFactory(),
            new HashMapTypeAdapterFactory(),
            new WildcardTypeAdapterFactory(),
        ]);

        /** @var HashMapTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new PhpType('Map'));

        /** @var HashMap $result */
        $result = $adapter->readFromJson('{"key": "value", "key2": "value2"}');

        self::assertInstanceOf(HashMap::class, $result);
        self::assertSame('value', $result->get('key'));
        self::assertSame('value2', $result->get('key2'));
    }

    public function testDeserializeNestedObject()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new StringTypeAdapterFactory(),
            new HashMapTypeAdapterFactory(),
            new WildcardTypeAdapterFactory(),
        ]);

        /** @var HashMapTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new PhpType('Map'));

        /** @var HashMap $result */
        $result = $adapter->readFromJson('{"key": {"nestedKey": "nestedValue"}}');

        self::assertInstanceOf(HashMap::class, $result);
        self::assertInstanceOf(HashMap::class, $result->get('key'));
        self::assertSame('nestedValue', $result->get('key')->get('nestedKey'));
    }

    public function testDeserializeNestedArray()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new StringTypeAdapterFactory(),
            new FloatTypeAdapterFactory(),
            new ArrayListTypeAdapterFactory(),
            new HashMapTypeAdapterFactory(),
            new WildcardTypeAdapterFactory(),
        ]);

        /** @var HashMapTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new PhpType('Map'));

        /** @var HashMap $result */
        $result = $adapter->readFromJson('{"key": [1]}');

        self::assertInstanceOf(HashMap::class, $result);
        self::assertInstanceOf(ArrayList::class, $result->get('key'));
        self::assertSame(1.0, $result->get('key')->get(0));
    }

    public function testDeserializeOneGenericType()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new StringTypeAdapterFactory(),
            new HashMapTypeAdapterFactory(),
            new WildcardTypeAdapterFactory(),
        ]);

        /** @var HashMapTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new PhpType('Map<string>'));

        /** @var HashMap $result */
        $result = $adapter->readFromJson('{"key": "value"}');

        self::assertInstanceOf(HashMap::class, $result);
        self::assertSame('value', $result->get('key'));
    }

    public function testDeserializeTwoGenericTypes()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new StringTypeAdapterFactory(),
            new HashMapTypeAdapterFactory(),
            new WildcardTypeAdapterFactory(),
        ]);

        /** @var HashMapTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new PhpType('Map<string, string>'));

        /** @var HashMap $result */
        $result = $adapter->readFromJson('{"key": "value"}');

        self::assertInstanceOf(HashMap::class, $result);
        self::assertSame('value', $result->get('key'));
    }

    public function testDeserializeThreeGenericTypes()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('HashMap must have one or two generic types');

        $typeAdapterProvider = new TypeAdapterProvider([
            new StringTypeAdapterFactory(),
            new HashMapTypeAdapterFactory(),
            new WildcardTypeAdapterFactory(),
        ]);

        /** @var HashMapTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new PhpType('Map<string, string, string>'));

        $adapter->readFromJson('{"key": "value"}');
    }

    public function testDeserializeNestedGenerics()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new StringTypeAdapterFactory(),
            new IntegerTypeAdapterFactory(),
            new ArrayListTypeAdapterFactory(),
            new HashMapTypeAdapterFactory(),
            new WildcardTypeAdapterFactory(),
        ]);

        /** @var HashMapTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new PhpType('Map<List<Map<string, integer>>>'));

        /** @var HashMap $result */
        $result = $adapter->readFromJson('{"key": [{"nestedKey": 12}]}');

        self::assertInstanceOf(HashMap::class, $result);
        self::assertInstanceOf(ArrayList::class, $result->get('key'));
        self::assertInstanceOf(HashMap::class, $result->get('key')->get(0));
        self::assertSame(12, $result->get('key')->get(0)->get('nestedKey'));
    }

    public function testSerializeNull()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new HashMapTypeAdapterFactory(),
        ]);

        /** @var HashMapTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new PhpType('Map'));

        self::assertSame('null', $adapter->writeToJson(null, false));
    }

    public function testSerializeSimple()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new StringTypeAdapterFactory(),
            new IntegerTypeAdapterFactory(),
            new HashMapTypeAdapterFactory(),
        ]);

        /** @var HashMapTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new PhpType('Map'));

        $hashMap = new HashMap();
        $hashMap->put('foo', 'bar');
        $hashMap->put('bar', 1);

        self::assertSame('{"foo":"bar","bar":1}', $adapter->writeToJson($hashMap, false));
    }

    public function testSerializeNestedObjects()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new StringTypeAdapterFactory(),
            new IntegerTypeAdapterFactory(),
            new ArrayListTypeAdapterFactory(),
            new HashMapTypeAdapterFactory(),
        ]);

        /** @var HashMapTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new PhpType('Map'));

        $hashMap = new HashMap([
            'key' => new HashMap(['foo' => 'bar']),
            'key2' => new ArrayList([1, 2, 3]),
        ]);

        self::assertSame('{"key":{"foo":"bar"},"key2":[1,2,3]}', $adapter->writeToJson($hashMap, false));
    }

    public function testSerializeOneGeneric()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new StringTypeAdapterFactory(),
            new IntegerTypeAdapterFactory(),
            new HashMapTypeAdapterFactory(),
        ]);

        /** @var HashMapTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new PhpType('Map<string>'));

        $hashMap = new HashMap();
        $hashMap->put('foo', 'bar');

        self::assertSame('{"foo":"bar"}', $adapter->writeToJson($hashMap, false));
    }

    public function testSerializeTwoGenerics()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new StringTypeAdapterFactory(),
            new IntegerTypeAdapterFactory(),
            new HashMapTypeAdapterFactory(),
        ]);

        /** @var HashMapTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new PhpType('Map<string, string>'));

        $hashMap = new HashMap();
        $hashMap->put('foo', 'bar');

        self::assertSame('{"foo":"bar"}', $adapter->writeToJson($hashMap, false));
    }

    public function testSerializeNestedGenerics()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new StringTypeAdapterFactory(),
            new IntegerTypeAdapterFactory(),
            new ArrayTypeAdapterFactory(),
            new HashMapTypeAdapterFactory(),
        ]);

        /** @var HashMapTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new PhpType('Map<string, array<int>>'));

        $hashMap = new HashMap();
        $hashMap->put('foo', [1, 2, 3]);

        self::assertSame('{"foo":[1,2,3]}', $adapter->writeToJson($hashMap, false));
    }

    public function testSerializeTooManyGenerics()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('HashMap must have one or two generic types');

        $typeAdapterProvider = new TypeAdapterProvider([
            new HashMapTypeAdapterFactory(),
        ]);

        /** @var HashMapTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new PhpType('Map<string, array, int>'));
        $adapter->writeToJson(new HashMap(), false);
    }
}
