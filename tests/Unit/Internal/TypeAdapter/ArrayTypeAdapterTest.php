<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter;

use Doctrine\Common\Cache\ArrayCache;
use LogicException;
use PHPUnit_Framework_TestCase;
use Tebru\Gson\Exception\UnexpectedJsonTokenException;
use Tebru\Gson\Internal\DefaultPhpType;
use Tebru\Gson\Internal\TypeAdapter\ArrayTypeAdapter;
use Tebru\Gson\Internal\TypeAdapter\Factory\ArrayTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\FloatTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\IntegerTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\NullTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\StringTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\WildcardTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapterProvider;

/**
 * Class ArrayTypeAdapterTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\TypeAdapter\ArrayTypeAdapter
 * @covers \Tebru\Gson\TypeAdapter
 */
class ArrayTypeAdapterTest extends PHPUnit_Framework_TestCase
{
    public function testDeserializeNull()
    {
        $typeAdapterProvider = new TypeAdapterProvider(
            [
                new ArrayTypeAdapterFactory(),
            ],
            new ArrayCache()
        );

        $adapter = $typeAdapterProvider->getAdapter(new DefaultPhpType('array'));

        $result = $adapter->readFromJson('null');

        self::assertNull($result);
    }

    public function testDeserializeSimpleArray()
    {
        $typeAdapterProvider = new TypeAdapterProvider(
            [
                new FloatTypeAdapterFactory(),
                new ArrayTypeAdapterFactory(),
                new WildcardTypeAdapterFactory(),
            ],
            new ArrayCache()
        );

        /** @var ArrayTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new DefaultPhpType('array'));
        $result = $adapter->readFromJson('[1, 2, 3]');

        self::assertSame([1.0, 2.0, 3.0], $result);
    }

    public function testDeserializeSimpleArrayAsInteger()
    {
        $typeAdapterProvider = new TypeAdapterProvider(
            [
                new IntegerTypeAdapterFactory(),
                new ArrayTypeAdapterFactory(),
                new WildcardTypeAdapterFactory(),
            ],
            new ArrayCache()
        );

        /** @var ArrayTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new DefaultPhpType('array<int>'));
        $result = $adapter->readFromJson('[1, 2, 3]');

        self::assertSame([1, 2, 3], $result);
    }

    public function testDeserializeNestedArray()
    {
        $typeAdapterProvider = new TypeAdapterProvider(
            [
                new IntegerTypeAdapterFactory(),
                new ArrayTypeAdapterFactory(),
                new WildcardTypeAdapterFactory(),
            ],
            new ArrayCache()
        );

        /** @var ArrayTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new DefaultPhpType('array<array<int>>'));
        $result = $adapter->readFromJson('[[1], [2], [3]]');

        self::assertSame([[1], [2], [3]], $result);
    }

    public function testDeserializeSimpleObject()
    {
        $typeAdapterProvider = new TypeAdapterProvider(
            [
                new StringTypeAdapterFactory(),
                new ArrayTypeAdapterFactory(),
                new WildcardTypeAdapterFactory(),
            ],
            new ArrayCache()
        );

        /** @var ArrayTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new DefaultPhpType('array'));
        $result = $adapter->readFromJson('{"key": "value"}');

        self::assertSame(['key' => 'value'], $result);
    }

    public function testDeserializeNestedObject()
    {
        $typeAdapterProvider = new TypeAdapterProvider(
            [
                new StringTypeAdapterFactory(),
                new ArrayTypeAdapterFactory(),
                new WildcardTypeAdapterFactory(),
            ],
            new ArrayCache()
        );

        /** @var ArrayTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new DefaultPhpType('array'));
        $result = $adapter->readFromJson('{"key": {"nestedKey": "nestedValue", "nestedKey2": "nestedValue2"}}');

        self::assertSame(['key' => ['nestedKey' => 'nestedValue', 'nestedKey2' => 'nestedValue2']], $result);
    }

    public function testDeserializeNestedObjectWithGeneric()
    {
        $typeAdapterProvider = new TypeAdapterProvider(
            [
                new StringTypeAdapterFactory(),
                new ArrayTypeAdapterFactory(),
                new WildcardTypeAdapterFactory(),
            ],
            new ArrayCache()
        );

        /** @var ArrayTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new DefaultPhpType('array<array>'));
        $result = $adapter->readFromJson('{"key": {"nestedKey": "nestedValue", "nestedKey2": "nestedValue2"}}');

        self::assertSame(['key' => ['nestedKey' => 'nestedValue', 'nestedKey2' => 'nestedValue2']], $result);
    }

    public function testDeserializeNestedObjectWithKeyAndValueTypes()
    {
        $typeAdapterProvider = new TypeAdapterProvider(
            [
                new IntegerTypeAdapterFactory(),
                new StringTypeAdapterFactory(),
                new ArrayTypeAdapterFactory(),
                new WildcardTypeAdapterFactory(),
            ],
            new ArrayCache()
        );

        /** @var ArrayTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new DefaultPhpType('array<string, array<string, string>>'));
        $result = $adapter->readFromJson('{"key": {"nestedKey": "nestedValue", "nestedKey2": "nestedValue2"}}');

        self::assertSame(['key' => ['nestedKey' => 'nestedValue', 'nestedKey2' => 'nestedValue2']], $result);
    }

    public function testDeserializeMoreThanTwoGenericTypes()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Array may not have more than 2 generic types');

        $adapter = new ArrayTypeAdapter(new DefaultPhpType('array<string, string, string>'), new TypeAdapterProvider([], new ArrayCache()));
        $adapter->readFromJson('{}');
    }

    public function testDeserializeMoreThanOneGenericTypeForArray()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('An array may only specify a generic type for the value');

        $adapter = new ArrayTypeAdapter(new DefaultPhpType('array<string, string>'), new TypeAdapterProvider([], new ArrayCache()));
        $adapter->readFromJson('[1]');
    }

    public function testDeserializeNonArrayOrObject()
    {
        $this->expectException(UnexpectedJsonTokenException::class);
        $this->expectExceptionMessage('Could not parse json, expected array or object but found "number"');

        $adapter = new ArrayTypeAdapter(new DefaultPhpType('array'), new TypeAdapterProvider([], new ArrayCache()));
        $adapter->readFromJson('1');
    }

    public function testSerializeNull()
    {
        $typeAdapterProvider = new TypeAdapterProvider(
            [
                new ArrayTypeAdapterFactory(),
            ],
            new ArrayCache()
        );

        /** @var ArrayTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new DefaultPhpType('array'));

        self::assertSame('null', $adapter->writeToJson(null, false));
    }

    public function testSerializeArrayInts()
    {
        $typeAdapterProvider = new TypeAdapterProvider(
            [
                new IntegerTypeAdapterFactory(),
                new ArrayTypeAdapterFactory(),
            ],
            new ArrayCache()
        );

        /** @var ArrayTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new DefaultPhpType('array'));

        self::assertSame('[1,2,3]', $adapter->writeToJson([1, 2, 3], false));
    }

    public function testSerializeArrayVariableTypes()
    {
        $typeAdapterProvider = new TypeAdapterProvider(
            [
                new StringTypeAdapterFactory(),
                new IntegerTypeAdapterFactory(),
                new ArrayTypeAdapterFactory(),
                new NullTypeAdapterFactory(),
            ],
            new ArrayCache()
        );

        /** @var ArrayTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new DefaultPhpType('array'));

        self::assertSame('[1,"foo"]', $adapter->writeToJson([1, 'foo', null], false));
    }

    public function testSerializeArrayVariableTypesNull()
    {
        $typeAdapterProvider = new TypeAdapterProvider(
            [
                new StringTypeAdapterFactory(),
                new IntegerTypeAdapterFactory(),
                new ArrayTypeAdapterFactory(),
                new NullTypeAdapterFactory(),
            ],
            new ArrayCache()
        );

        /** @var ArrayTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new DefaultPhpType('array'));

        self::assertSame('[1,"foo",null]', $adapter->writeToJson([1, 'foo', null], true));
    }

    public function testSerializeArrayAsObject()
    {
        $typeAdapterProvider = new TypeAdapterProvider(
            [
                new StringTypeAdapterFactory(),
                new IntegerTypeAdapterFactory(),
                new ArrayTypeAdapterFactory(),
            ],
            new ArrayCache()
        );

        /** @var ArrayTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new DefaultPhpType('array'));

        self::assertSame('{"foo":"bar","bar":1}', $adapter->writeToJson(['foo' => 'bar', 'bar' => 1], false));
    }

    public function testSerializeArrayOneGenericType()
    {
        $typeAdapterProvider = new TypeAdapterProvider(
            [
                new StringTypeAdapterFactory(),
                new IntegerTypeAdapterFactory(),
                new ArrayTypeAdapterFactory(),
            ],
            new ArrayCache()
        );

        /** @var ArrayTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new DefaultPhpType('array<int>'));

        self::assertSame('[1,2,3]', $adapter->writeToJson([1, 2, 3], false));
    }

    public function testSerializeArrayAsObjectOneGenericType()
    {
        $typeAdapterProvider = new TypeAdapterProvider(
            [
                new StringTypeAdapterFactory(),
                new IntegerTypeAdapterFactory(),
                new ArrayTypeAdapterFactory(),
            ],
            new ArrayCache()
        );

        /** @var ArrayTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new DefaultPhpType('array<string>'));

        self::assertSame('{"foo":"bar"}', $adapter->writeToJson(['foo' => 'bar'], false));
    }

    public function testSerializeArrayTwoGenericTypes()
    {
        $typeAdapterProvider = new TypeAdapterProvider(
            [
                new StringTypeAdapterFactory(),
                new IntegerTypeAdapterFactory(),
                new ArrayTypeAdapterFactory(),
            ],
            new ArrayCache()
        );

        /** @var ArrayTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new DefaultPhpType('array<string, string>'));

        self::assertSame('{"foo":"bar"}', $adapter->writeToJson(['foo' => 'bar'], false));
    }

    public function testSerializeTooManyGenerics()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Array may not have more than 2 generic types');

        $adapter = new ArrayTypeAdapter(new DefaultPhpType('array<int, string, int>'), new TypeAdapterProvider([], new ArrayCache()));
        $adapter->writeToJson([], false);
    }
}
