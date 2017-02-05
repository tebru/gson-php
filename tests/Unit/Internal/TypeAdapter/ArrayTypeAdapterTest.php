<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter;

use LogicException;
use PHPUnit_Framework_TestCase;
use Tebru\Collection\HashMap;
use Tebru\Gson\Exception\UnexpectedJsonTokenException;
use Tebru\Gson\Internal\PhpType;
use Tebru\Gson\Internal\TypeAdapter\ArrayTypeAdapter;
use Tebru\Gson\Internal\TypeAdapter\Factory\ArrayTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\FloatTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\HashMapTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\IntegerTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\StringTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\WildcardTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapterProvider;

/**
 * Class ArrayTypeAdapterTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\TypeAdapter\ArrayTypeAdapter
 */
class ArrayTypeAdapterTest extends PHPUnit_Framework_TestCase
{
    public function testNull()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new ArrayTypeAdapterFactory(),
        ]);

        $adapter = $typeAdapterProvider->getAdapter(new PhpType('array'));

        $result = $adapter->readFromJson('null');

        self::assertNull($result);
    }

    public function testSimpleArray()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new FloatTypeAdapterFactory(),
            new ArrayTypeAdapterFactory(),
            new WildcardTypeAdapterFactory(),
        ]);

        /** @var ArrayTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new PhpType('array'));
        $result = $adapter->readFromJson('[1, 2, 3]');

        self::assertSame([1.0, 2.0, 3.0], $result);
    }

    public function testSimpleArrayAsInteger()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new IntegerTypeAdapterFactory(),
            new ArrayTypeAdapterFactory(),
            new WildcardTypeAdapterFactory(),
        ]);

        /** @var ArrayTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new PhpType('array<int>'));
        $result = $adapter->readFromJson('[1, 2, 3]');

        self::assertSame([1, 2, 3], $result);
    }

    public function testNestedArray()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new IntegerTypeAdapterFactory(),
            new ArrayTypeAdapterFactory(),
            new WildcardTypeAdapterFactory(),
        ]);

        /** @var ArrayTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new PhpType('array<array<int>>'));
        $result = $adapter->readFromJson('[[1], [2], [3]]');

        self::assertSame([[1], [2], [3]], $result);
    }

    public function testSimpleObject()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new StringTypeAdapterFactory(),
            new ArrayTypeAdapterFactory(),
            new WildcardTypeAdapterFactory(),
        ]);

        /** @var ArrayTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new PhpType('array'));
        $result = $adapter->readFromJson('{"key": "value"}');

        self::assertSame(['key' => 'value'], $result);
    }

    public function testNestedObject()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new StringTypeAdapterFactory(),
            new ArrayTypeAdapterFactory(),
            new WildcardTypeAdapterFactory(),
        ]);

        /** @var ArrayTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new PhpType('array'));
        $result = $adapter->readFromJson('{"key": {"nestedKey": "nestedValue", "nestedKey2": "nestedValue2"}}');

        self::assertSame(['key' => ['nestedKey' => 'nestedValue', 'nestedKey2' => 'nestedValue2']], $result);
    }

    public function testNestedObjectWithGeneric()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new StringTypeAdapterFactory(),
            new ArrayTypeAdapterFactory(),
            new HashMapTypeAdapterFactory(),
            new WildcardTypeAdapterFactory(),
        ]);

        /** @var ArrayTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new PhpType('array<Map>'));
        $result = $adapter->readFromJson('{"key": {"nestedKey": "nestedValue", "nestedKey2": "nestedValue2"}}');

        /** @var HashMap $map */
        $map = $result['key'];

        self::assertInstanceOf(HashMap::class, $map);
        self::assertSame('nestedValue', $map->get('nestedKey'));
        self::assertSame('nestedValue2', $map->get('nestedKey2'));
    }

    public function testNestedObjectWithKeyAndValueTypes()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new IntegerTypeAdapterFactory(),
            new StringTypeAdapterFactory(),
            new ArrayTypeAdapterFactory(),
            new HashMapTypeAdapterFactory(),
            new WildcardTypeAdapterFactory(),
        ]);

        /** @var ArrayTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new PhpType('array<string, Map<string, string>>'));
        $result = $adapter->readFromJson('{"key": {"nestedKey": "nestedValue", "nestedKey2": "nestedValue2"}}');

        /** @var HashMap $map */
        $map = $result['key'];

        self::assertInstanceOf(HashMap::class, $map);
        self::assertSame('nestedValue', $map->get('nestedKey'));
        self::assertSame('nestedValue2', $map->get('nestedKey2'));
    }

    public function testMoreThanTwoGenericTypes()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Array may not have more than 2 generic types');

        $adapter = new ArrayTypeAdapter(new PhpType('array<string, string, string>'), new TypeAdapterProvider([]));
        $adapter->readFromJson('{}');
    }

    public function testMoreThanOneGenericTypeForArray()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('An array may only specify a generic type for the value');

        $adapter = new ArrayTypeAdapter(new PhpType('array<string, string>'), new TypeAdapterProvider([]));
        $adapter->readFromJson('[1]');
    }

    public function testNonArrayOrObject()
    {
        $this->expectException(UnexpectedJsonTokenException::class);
        $this->expectExceptionMessage('Could not parse json, expected array or object but found "number"');

        $adapter = new ArrayTypeAdapter(new PhpType('array'), new TypeAdapterProvider([]));
        $adapter->readFromJson('1');
    }
}
