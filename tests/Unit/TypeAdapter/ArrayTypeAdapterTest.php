<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\TypeAdapter;

use LogicException;
use PHPUnit\Framework\TestCase;
use stdClass;
use Tebru\Gson\TypeAdapter\ArrayTypeAdapter;
use Tebru\Gson\Exception\JsonSyntaxException;
use Tebru\Gson\TypeAdapter\IntegerTypeAdapter;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\TypeAdapter\StringTypeAdapter;
use Tebru\Gson\Test\MockProvider;
use Tebru\PhpType\TypeToken;

/**
 * Class ArrayTypeAdapterTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\TypeAdapter\ArrayTypeAdapter
 * @covers \Tebru\Gson\TypeAdapter
 */
class ArrayTypeAdapterTest extends TestCase
{
    /**
     * @var TypeAdapterProvider
     */
    private $typeAdapterProvider;
    
    public function setUp()
    {
        $this->typeAdapterProvider = MockProvider::typeAdapterProvider(MockProvider::excluder());
    }
    
    public function testDeserializeNull(): void
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array'));

        $result = $adapter->readFromJson('null');

        self::assertNull($result);
    }

    public function testDeserializeSimpleArray(): void
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array'));
        $result = $adapter->readFromJson('[1, 2, 3]');

        self::assertSame([1.0, 2.0, 3.0], $result);
    }

    public function testDeserializeSimpleArrayAsInteger(): void
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array<int>'));
        $result = $adapter->readFromJson('[1, 2, 3]');

        self::assertSame([1, 2, 3], $result);
    }

    public function testDeserializeNestedArray(): void
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array<array<int>>'));
        $result = $adapter->readFromJson('[[1], [2], [3]]');

        self::assertSame([[1], [2], [3]], $result);
    }

    public function testDeserializeSimpleObject(): void
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array'));
        $result = $adapter->readFromJson('{"key": "value"}');

        self::assertSame(['key' => 'value'], $result);
    }

    public function testDeserializeNestedObject(): void
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array'));
        $result = $adapter->readFromJson('{"key": {"nestedKey": "nestedValue", "nestedKey2": "nestedValue2"}}');

        self::assertSame(['key' => ['nestedKey' => 'nestedValue', 'nestedKey2' => 'nestedValue2']], $result);
    }

    public function testDeserializeNestedObjectWithGeneric(): void
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array<array>'));
        $result = $adapter->readFromJson('{"key": {"nestedKey": "nestedValue", "nestedKey2": "nestedValue2"}}');

        self::assertSame(['key' => ['nestedKey' => 'nestedValue', 'nestedKey2' => 'nestedValue2']], $result);
    }

    public function testDeserializeNestedObjectWithKeyAndValueTypes(): void
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array<string, array<string, string>>'));
        $result = $adapter->readFromJson('{"key": {"nestedKey": "nestedValue", "nestedKey2": "nestedValue2"}}');

        self::assertSame(['key' => ['nestedKey' => 'nestedValue', 'nestedKey2' => 'nestedValue2']], $result);
    }

    public function testDeserializeArrayWithNonStringOrIntegerKey(): void
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

    public function testDeserializeArrayWithIntegerKeyPassedString(): void
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

    public function testDeserializeArrayWithIntegerKey(): void
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array<int, string>'));
        $result = $adapter->readFromJson('{"1": "foo"}');

        self::assertSame([1 => 'foo'], $result);
    }

    public function testDeserializeMoreThanTwoGenericTypes(): void
    {
        $adapter = new ArrayTypeAdapter($this->typeAdapterProvider, TypeToken::create('string'), new StringTypeAdapter(), 3);
        try {
            $adapter->readFromJson('{}');
        } catch (LogicException $exception) {
            self::assertSame('Array may not have more than 2 generic types at "$"', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testDeserializeMoreThanOneGenericTypeForArray(): void
    {
        $adapter = new ArrayTypeAdapter($this->typeAdapterProvider, TypeToken::create('string'), new StringTypeAdapter(), 2);
        try {
            $adapter->readFromJson('[1]');
        } catch (LogicException $exception) {
            self::assertSame('An array may only specify a generic type for the value at "$"', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testDeserializeNonArrayOrObject(): void
    {
        $adapter = new ArrayTypeAdapter($this->typeAdapterProvider, TypeToken::create('?'), new IntegerTypeAdapter(), 1);
        try {
            $adapter->readFromJson('1');
        } catch (JsonSyntaxException $exception) {
            self::assertSame('Could not parse json, expected array or object but found "number" at "$"', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testSerializeNull(): void
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array'));

        self::assertSame('null', $adapter->writeToJson(null, false));
    }

    public function testSerializeArrayInts(): void
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array'));

        self::assertSame('[1,2,3]', $adapter->writeToJson([1, 2, 3], false));
    }

    public function testSerializeArrayVariableTypes(): void
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array'));

        self::assertSame('[1,"foo"]', $adapter->writeToJson([1, 'foo', null], false));
    }

    public function testSerializeArrayVariableTypesNull(): void
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array'));

        self::assertSame('[1,"foo",null]', $adapter->writeToJson([1, 'foo', null], true));
    }

    public function testSerializeArrayAsObject(): void
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array'));

        self::assertSame('{"foo":"bar","bar":1}', $adapter->writeToJson(['foo' => 'bar', 'bar' => 1], false));
    }

    public function testSerializeStdClass(): void
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array'));

        $write = new stdClass();
        $write->foo = 'bar';
        $write->bar = 1;

        self::assertSame('{"foo":"bar","bar":1}', $adapter->writeToJson($write));
    }

    public function testSerializeArrayOneGenericType(): void
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array<int>'));

        self::assertSame('[1,2,3]', $adapter->writeToJson([1, 2, 3], false));
    }

    public function testSerializeArrayAsObjectOneGenericType(): void
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array<string>'));

        self::assertSame('{"foo":"bar"}', $adapter->writeToJson(['foo' => 'bar'], false));
    }

    public function testSerializeArrayTwoGenericTypes(): void
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array<string, string>'));

        self::assertSame('{"foo":"bar"}', $adapter->writeToJson(['foo' => 'bar'], false));
    }

    public function testSerializeTooManyGenerics(): void
    {
        $adapter = new ArrayTypeAdapter($this->typeAdapterProvider, TypeToken::create('string'), new StringTypeAdapter(), 3);
        try {
            $adapter->writeToJson([], false);
        } catch (LogicException $exception) {
            self::assertSame('Array may not have more than 2 generic types at "$"', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }
}
