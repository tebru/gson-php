<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\TypeAdapter;

use LogicException;
use stdClass;
use Tebru\Gson\Test\Mock\ExcludedClass;
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
 * @covers \Tebru\Gson\TypeAdapter\ScalarArrayTypeAdapter
 * @covers \Tebru\Gson\TypeAdapter
 */
class ArrayTypeAdapterTest extends TypeAdapterTestCase
{
    /**
     * @var TypeAdapterProvider
     */
    private $typeAdapterProvider;
    
    public function setUp()
    {
        parent::setUp();

        $this->typeAdapterProvider = MockProvider::typeAdapterProvider(MockProvider::excluder());
    }
    
    public function testDeserializeNull(): void
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array'));

        $result = $adapter->read(json_decode('null', true), $this->readerContext);

        self::assertNull($result);
    }

    public function testDeserializeSimpleArray(): void
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array'));
        $result = $adapter->read(json_decode('[1, 2, 3]', true), $this->readerContext);

        self::assertSame([1, 2, 3], $result);
    }

    public function testDeserializeEmptyArray(): void
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array'));
        $result = $adapter->read(json_decode('[]', true), $this->readerContext);

        self::assertSame([], $result);
    }

    public function testDeserializeSimpleArrayWithNull(): void
    {
        $this->disableTypeAdaptersForReader();

        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array'));
        $result = $adapter->read(json_decode('[1, 2, null]', true), $this->readerContext);

        self::assertSame([1, 2, null], $result);
    }

    public function testDeserializeSimpleObjectsWithoutScalarTypeAdapter(): void
    {
        $this->disableTypeAdaptersForReader();

        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array'));
        $result = $adapter->read(json_decode('[1, 2, 3]', true), $this->readerContext);

        self::assertSame([1, 2, 3], $result);
    }

    public function testDeserializeSimpleArrayAsInteger(): void
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array<int>'));
        $result = $adapter->read(json_decode('[1, 2, 3]', true), $this->readerContext);

        self::assertSame([1, 2, 3], $result);
    }

    public function testDeserializeNestedArray(): void
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array<array<int>>'));
        $result = $adapter->read(json_decode('[[1], [2], [3]]', true), $this->readerContext);

        self::assertSame([[1], [2], [3]], $result);
    }

    public function testDeserializeSimpleObject(): void
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array'));
        $result = $adapter->read(json_decode('{"key": "value"}', true), $this->readerContext);

        self::assertSame(['key' => 'value'], $result);
    }

    public function testDeserializeSimpleObjectWithNull(): void
    {
        $this->disableTypeAdaptersForReader();

        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array'));
        $result = $adapter->read(json_decode('{"key": "value", "key2": null}', true), $this->readerContext);

        self::assertSame(['key' => 'value', 'key2' => null], $result);
    }

    public function testDeserializeSimpleObjectWithGenerics(): void
    {
        $this->disableTypeAdaptersForReader();

        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array<string, string>'));
        $result = $adapter->read(json_decode('{"key": "value", "key2": null}', true), $this->readerContext);

        self::assertSame(['key' => 'value', 'key2' => null], $result);
    }

    public function testDeserializeNestedObject(): void
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array'));
        $result = $adapter->read(json_decode('{"key": {"nestedKey": "nestedValue", "nestedKey2": "nestedValue2"}}', true), $this->readerContext);

        self::assertSame(['key' => ['nestedKey' => 'nestedValue', 'nestedKey2' => 'nestedValue2']], $result);
    }

    public function testDeserializeNestedObjectWithGeneric(): void
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array<array>'));
        $result = $adapter->read(json_decode('{"key": {"nestedKey": "nestedValue", "nestedKey2": "nestedValue2"}}', true), $this->readerContext);

        self::assertSame(['key' => ['nestedKey' => 'nestedValue', 'nestedKey2' => 'nestedValue2']], $result);
    }

    public function testDeserializeNestedObjectWithKeyAndValueTypes(): void
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array<string, array<string, string>>'));
        $result = $adapter->read(json_decode('{"key": {"nestedKey": "nestedValue", "nestedKey2": "nestedValue2"}}', true), $this->readerContext);

        self::assertSame(['key' => ['nestedKey' => 'nestedValue', 'nestedKey2' => 'nestedValue2']], $result);
    }

    public function testDeserializeNestedObjectWithKeyAndValueTypesWithoutScalarTypeAdapters(): void
    {
        $this->disableTypeAdaptersForReader();

        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array<string, array<string, string>>'));
        $result = $adapter->read(json_decode('{"key": {"nestedKey": "nestedValue", "nestedKey2": "nestedValue2"}}', true), $this->readerContext);

        self::assertSame(['key' => ['nestedKey' => 'nestedValue', 'nestedKey2' => 'nestedValue2']], $result);
    }

    public function testDeserializeArrayWithNonStringOrIntegerKey(): void
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array<float, string>'));
        try {
            $adapter->read(json_decode('{"1.1": "foo"}', true), $this->readerContext);
        } catch (LogicException $exception) {
            self::assertSame('Array keys must be strings or integers', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testDeserializeArrayWithIntegerKeyPassedString(): void
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array<int, string>'));
        try {
            $adapter->read(json_decode('{"asdf": "foo"}', true), $this->readerContext);
        } catch (JsonSyntaxException $exception) {
            self::assertSame('Expected integer, but found string for key', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testDeserializeArrayWithIntegerKey(): void
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array<int, string>'));
        $result = $adapter->read(json_decode('["foo"]', true), $this->readerContext);

        self::assertSame(['foo'], $result);
    }

    public function testDeserializeMoreThanTwoGenericTypes(): void
    {
        $adapter = new ArrayTypeAdapter($this->typeAdapterProvider, TypeToken::create('string'), new StringTypeAdapter(), 3);
        try {
            $adapter->read(json_decode('{}', true), $this->readerContext);
        } catch (LogicException $exception) {
            self::assertSame('Array may not have more than 2 generic types', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testDeserializeNonArrayOrObject(): void
    {
        $adapter = new ArrayTypeAdapter($this->typeAdapterProvider, TypeToken::create('?'), new IntegerTypeAdapter(), 1);
        try {
            $adapter->read(json_decode('1', true), $this->readerContext);
        } catch (JsonSyntaxException $exception) {
            self::assertSame('Could not parse json, expected array or object but found "integer"', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testSerializeNullArray(): void
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array'));

        self::assertNull($adapter->write(null, $this->writerContext));
    }

    public function testSerializeNull(): void
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array'));

        self::assertSame([], $adapter->write([new ExcludedClass()], $this->writerContext));
    }

    public function testSerializeArrayInts(): void
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array'));

        self::assertSame([1, 2, 3], $adapter->write([1, 2, 3], $this->writerContext));
    }

    public function testSerializeEmptyArray(): void
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array'));

        self::assertSame([], $adapter->write([], $this->writerContext));
    }

    public function testSerializeArrayIntsWithoutScalarTypeAdapters(): void
    {
        $this->disableScalarTypeAdaptersForWriter();

        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array'));

        self::assertSame([1, 2, 3], $adapter->write([1, 2, 3], $this->writerContext));
    }

    public function testSerializNestedeArrayIntsWithoutScalarTypeAdapters(): void
    {
        $this->disableScalarTypeAdaptersForWriter();

        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array'));

        self::assertSame([[1, 2, 3]], $adapter->write([[1, 2, 3]], $this->writerContext));
    }

    public function testSerializeArrayVariableTypes(): void
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array'));

        self::assertSame([1, 'foo'], $adapter->write([1, 'foo', null], $this->writerContext));
    }

    public function testSerializeArrayVariableTypesNull(): void
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array'));
        $this->writerContext->setSerializeNull(true);

        self::assertSame([1, 'foo', null], $adapter->write([1, 'foo', null], $this->writerContext));
    }

    public function testSerializeArrayAsObject(): void
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array'));

        self::assertSame(['foo' => 'bar', 'bar' => 1], $adapter->write(['foo' => 'bar', 'bar' => 1], $this->writerContext));
    }

    public function testSerializeStdClass(): void
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array'));

        $write = new stdClass();
        $write->foo = 'bar';
        $write->bar = 1;

        self::assertSame(['foo' => 'bar','bar' => 1], $adapter->write($write, $this->writerContext));
    }

    public function testSerializeArrayOneGenericType(): void
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array<int>'));

        self::assertSame([1,2,3], $adapter->write([1, 2, 3], $this->writerContext));
    }

    public function testSerializeArrayAsObjectOneGenericType(): void
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array<string>'));

        self::assertSame(['foo' => 'bar'], $adapter->write(['foo' => 'bar'], $this->writerContext));
    }

    public function testSerializeArrayTwoGenericTypes(): void
    {
        /** @var ArrayTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('array<string, string>'));

        self::assertSame(['foo' => 'bar'], $adapter->write(['foo' => 'bar'], $this->writerContext));
    }

    public function testSerializeTooManyGenerics(): void
    {
        $adapter = new ArrayTypeAdapter($this->typeAdapterProvider, TypeToken::create('string'), new StringTypeAdapter(), 3);
        try {
            $adapter->write([], $this->writerContext);
        } catch (LogicException $exception) {
            self::assertSame('Array may not have more than 2 generic types', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    /**
     * Disable scalar type adapters for reader
     */
    private function disableTypeAdaptersForReader(): void
    {
        $this->readerContext->setEnableScalarAdapters(false);
        $this->typeAdapterProvider = MockProvider::typeAdapterProvider(MockProvider::excluder(), [], null, false);
    }

    /**
     * Disable scalar type adapters for writer
     */
    private function disableScalarTypeAdaptersForWriter(): void
    {
        $this->writerContext->setEnableScalarAdapters(false);
        $this->typeAdapterProvider = MockProvider::typeAdapterProvider(MockProvider::excluder(), [], null, false);
    }
}
