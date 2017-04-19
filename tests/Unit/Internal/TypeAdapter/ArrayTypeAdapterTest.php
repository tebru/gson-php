<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter;

use LogicException;
use PHPUnit_Framework_TestCase;
use Tebru\Gson\Exception\JsonSyntaxException;
use Tebru\Gson\Internal\TypeAdapter\ArrayTypeAdapter;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\Test\MockProvider;
use Tebru\PhpType\TypeToken;

/**
 * Class ArrayTypeAdapterTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\TypeAdapter\ArrayTypeAdapter
 * @covers \Tebru\Gson\TypeAdapter
 */
class ArrayTypeAdapterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var TypeAdapterProvider
     */
    private $typeAdapterProvider;
    
    public function setUp()
    {
        $this->typeAdapterProvider = MockProvider::typeAdapterProvider(MockProvider::excluder());
    }
    
    public function testDeserializeNull()
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array'));

        $result = $adapter->readFromJson('null');

        self::assertNull($result);
    }

    public function testDeserializeSimpleArray()
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array'));
        $result = $adapter->readFromJson('[1, 2, 3]');

        self::assertSame([1.0, 2.0, 3.0], $result);
    }

    public function testDeserializeSimpleArrayAsInteger()
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array<int>'));
        $result = $adapter->readFromJson('[1, 2, 3]');

        self::assertSame([1, 2, 3], $result);
    }

    public function testDeserializeNestedArray()
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array<array<int>>'));
        $result = $adapter->readFromJson('[[1], [2], [3]]');

        self::assertSame([[1], [2], [3]], $result);
    }

    public function testDeserializeSimpleObject()
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array'));
        $result = $adapter->readFromJson('{"key": "value"}');

        self::assertSame(['key' => 'value'], $result);
    }

    public function testDeserializeNestedObject()
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array'));
        $result = $adapter->readFromJson('{"key": {"nestedKey": "nestedValue", "nestedKey2": "nestedValue2"}}');

        self::assertSame(['key' => ['nestedKey' => 'nestedValue', 'nestedKey2' => 'nestedValue2']], $result);
    }

    public function testDeserializeNestedObjectWithGeneric()
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array<array>'));
        $result = $adapter->readFromJson('{"key": {"nestedKey": "nestedValue", "nestedKey2": "nestedValue2"}}');

        self::assertSame(['key' => ['nestedKey' => 'nestedValue', 'nestedKey2' => 'nestedValue2']], $result);
    }

    public function testDeserializeNestedObjectWithKeyAndValueTypes()
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array<string, array<string, string>>'));
        $result = $adapter->readFromJson('{"key": {"nestedKey": "nestedValue", "nestedKey2": "nestedValue2"}}');

        self::assertSame(['key' => ['nestedKey' => 'nestedValue', 'nestedKey2' => 'nestedValue2']], $result);
    }

    public function testDeserializeArrayWithNonStringOrIntegerKey()
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array<float, string>'));
        try {
            $adapter->readFromJson('{"1.1": "foo"}');
        } catch (LogicException $exception) {
            self::assertSame('Array keys must be strings or integers at "$.1.1"', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testDeserializeArrayWithIntegerKeyPassedString()
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array<int, string>'));
        try {
            $adapter->readFromJson('{"asdf": "foo"}');
        } catch (JsonSyntaxException $exception) {
            self::assertSame('Expected integer, but found string for key at "$.asdf"', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testDeserializeArrayWithIntegerKey()
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array<int, string>'));
        $result = $adapter->readFromJson('{"1": "foo"}');

        self::assertSame([1 => 'foo'], $result);
    }

    public function testDeserializeMoreThanTwoGenericTypes()
    {
        $adapter = new ArrayTypeAdapter(new TypeToken('array<string, string, string>'), $this->typeAdapterProvider);
        try {
            $adapter->readFromJson('{}');
        } catch (LogicException $exception) {
            self::assertSame('Array may not have more than 2 generic types at "$"', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testDeserializeMoreThanOneGenericTypeForArray()
    {
        $adapter = new ArrayTypeAdapter(new TypeToken('array<string, string>'), $this->typeAdapterProvider);
        try {
            $adapter->readFromJson('[1]');
        } catch (LogicException $exception) {
            self::assertSame('An array may only specify a generic type for the value at "$[0]"', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testDeserializeNonArrayOrObject()
    {
        $adapter = new ArrayTypeAdapter(new TypeToken('array'), $this->typeAdapterProvider);
        try {
            $adapter->readFromJson('1');
        } catch (JsonSyntaxException $exception) {
            self::assertSame('Could not parse json, expected array or object but found "number" at "$"', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testSerializeNull()
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array'));

        self::assertSame('null', $adapter->writeToJson(null, false));
    }

    public function testSerializeArrayInts()
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array'));

        self::assertSame('[1,2,3]', $adapter->writeToJson([1, 2, 3], false));
    }

    public function testSerializeArrayVariableTypes()
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array'));

        self::assertSame('[1,"foo"]', $adapter->writeToJson([1, 'foo', null], false));
    }

    public function testSerializeArrayVariableTypesNull()
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array'));

        self::assertSame('[1,"foo",null]', $adapter->writeToJson([1, 'foo', null], true));
    }

    public function testSerializeArrayAsObject()
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array'));

        self::assertSame('{"foo":"bar","bar":1}', $adapter->writeToJson(['foo' => 'bar', 'bar' => 1], false));
    }

    public function testSerializeArrayOneGenericType()
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array<int>'));

        self::assertSame('[1,2,3]', $adapter->writeToJson([1, 2, 3], false));
    }

    public function testSerializeArrayAsObjectOneGenericType()
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array<string>'));

        self::assertSame('{"foo":"bar"}', $adapter->writeToJson(['foo' => 'bar'], false));
    }

    public function testSerializeArrayTwoGenericTypes()
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array<string, string>'));

        self::assertSame('{"foo":"bar"}', $adapter->writeToJson(['foo' => 'bar'], false));
    }

    public function testSerializeTooManyGenerics()
    {
        $adapter = new ArrayTypeAdapter(new TypeToken('array<int, string, int>'), $this->typeAdapterProvider);
        try {
            $adapter->writeToJson([], false);
        } catch (LogicException $exception) {
            self::assertSame('Array may not have more than 2 generic types', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }
}
