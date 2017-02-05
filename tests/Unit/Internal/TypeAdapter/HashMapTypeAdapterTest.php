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
    public function testNull()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new HashMapTypeAdapterFactory(),
        ]);

        $adapter = $typeAdapterProvider->getAdapter(new PhpType('Map'));

        $result = $adapter->readFromJson('null');

        self::assertNull($result);
    }

    public function testSimpleObject()
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

    public function testObjectMultipleKeys()
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

    public function testNestedObject()
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

    public function testNestedArray()
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

    public function testOneGenericType()
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

    public function testTwoGenericTypes()
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

    public function testThreeGenericTypes()
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

    public function testNestedGenerics()
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
}
