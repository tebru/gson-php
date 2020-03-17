<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\TypeAdapter\Factory;


use LogicException;
use PHPUnit\Framework\TestCase;
use Tebru\Gson\TypeAdapter\ArrayTypeAdapter;
use Tebru\Gson\TypeAdapter\Factory\ArrayTypeAdapterFactory;
use Tebru\Gson\TypeAdapter\IntegerTypeAdapter;
use Tebru\Gson\Test\MockProvider;
use Tebru\Gson\TypeAdapter\ScalarArrayTypeAdapter;
use Tebru\Gson\TypeAdapter\TypedArrayTypeAdapter;
use Tebru\Gson\TypeAdapter\WildcardTypeAdapter;
use Tebru\PhpType\TypeToken;

/**
 * Class ArrayTypeAdapterFactoryTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\TypeAdapter\Factory\ArrayTypeAdapterFactory
 */
class ArrayTypeAdapterFactoryTest extends TestCase
{
    /**
     * @dataProvider enableScalarTypeAdapters
     * @param bool $enable
     */
    public function testInvalidSupports(bool $enable): void
    {
        $factory = new ArrayTypeAdapterFactory($enable);
        $phpType = new TypeToken('string');
        $typeAdapterProvider = MockProvider::typeAdapterProvider(null, [], null, $enable);
        $adapter = $factory->create($phpType, $typeAdapterProvider);

        self::assertNull($adapter);
    }

    /**
     * @dataProvider enableScalarTypeAdapters
     * @param bool $enable
     */
    public function testCreate(bool $enable): void
    {
        $factory = new ArrayTypeAdapterFactory($enable);
        $phpType = new TypeToken('array');
        $typeAdapterProvider = MockProvider::typeAdapterProvider(null, [], null, $enable);
        $adapter = $factory->create($phpType, $typeAdapterProvider);

        self::assertInstanceOf(ArrayTypeAdapter::class, $adapter);
    }

    public function testCreateScalar(): void
    {
        $factory = new ArrayTypeAdapterFactory(false);
        $phpType = new TypeToken('array<int>');
        $typeAdapterProvider = MockProvider::typeAdapterProvider();
        $adapter = $factory->create($phpType, $typeAdapterProvider);

        self::assertInstanceOf(ScalarArrayTypeAdapter::class, $adapter);
    }

    /**
     * @dataProvider enableScalarTypeAdapters
     * @param bool $enable
     */
    public function testCreateStdClass(bool $enable): void
    {
        $factory = new ArrayTypeAdapterFactory($enable);
        $phpType = new TypeToken('stdClass');
        $typeAdapterProvider = MockProvider::typeAdapterProvider(null, [], null, $enable);
        $adapter = $factory->create($phpType, $typeAdapterProvider);

        self::assertInstanceOf(ArrayTypeAdapter::class, $adapter);
    }

    public function testCreateOneGenericType(): void
    {
        $factory = new ArrayTypeAdapterFactory(true);
        $phpType = new TypeToken('array<int>');
        $typeAdapterProvider = MockProvider::typeAdapterProvider();
        $adapter = $factory->create($phpType, $typeAdapterProvider);

        self::assertInstanceOf(TypedArrayTypeAdapter::class, $adapter);
    }

    public function testCreateOneGenericTypeWithoutScalarTypeAdapters(): void
    {
        $factory = new ArrayTypeAdapterFactory(false);
        $phpType = new TypeToken('array<int>');
        $typeAdapterProvider = MockProvider::typeAdapterProvider(null, [], null, false);
        $adapter = $factory->create($phpType, $typeAdapterProvider);

        self::assertInstanceOf(ScalarArrayTypeAdapter::class, $adapter);
    }

    public function testCreateTwoGenericTypes(): void
    {
        $factory = new ArrayTypeAdapterFactory(true);
        $phpType = new TypeToken('array<string, int>');
        $typeAdapterProvider = MockProvider::typeAdapterProvider();
        $adapter = $factory->create($phpType, $typeAdapterProvider);

        self::assertInstanceOf(TypedArrayTypeAdapter::class, $adapter);
    }

    public function testCreateTwoGenericTypesWithoutScalarTypeAdapters(): void
    {
        $factory = new ArrayTypeAdapterFactory(false);
        $phpType = new TypeToken('array<string, int>');
        $typeAdapterProvider = MockProvider::typeAdapterProvider(null, [], null, false);
        $adapter = $factory->create($phpType, $typeAdapterProvider);

        self::assertInstanceOf(ScalarArrayTypeAdapter::class, $adapter);
    }

    /**
     * @dataProvider enableScalarTypeAdapters
     * @param bool $enable
     */
    public function testCreateNested(bool $enable): void
    {
        $factory = new ArrayTypeAdapterFactory($enable);
        $phpType = new TypeToken('array<array>');
        $typeAdapterProvider = MockProvider::typeAdapterProvider(null, [], null, $enable);
        $adapter = $factory->create($phpType, $typeAdapterProvider);

        self::assertInstanceOf(TypedArrayTypeAdapter::class, $adapter);
        self::assertAttributeInstanceOf(ArrayTypeAdapter::class, 'valueTypeAdapter', $adapter);
    }

    /**
     * @dataProvider enableScalarTypeAdapters
     * @param bool $enable
     */
    public function testCreateNestedOneGeneric(bool $enable): void
    {
        $factory = new ArrayTypeAdapterFactory($enable);
        $phpType = new TypeToken('array<array<int>>');
        $typeAdapterProvider = MockProvider::typeAdapterProvider(null, [], null, $enable);
        $adapter = $factory->create($phpType, $typeAdapterProvider);

        self::assertInstanceOf(TypedArrayTypeAdapter::class, $adapter);
        if ($enable) {
            self::assertAttributeInstanceOf(TypedArrayTypeAdapter::class, 'valueTypeAdapter', $adapter);
        } else {
            self::assertAttributeInstanceOf(ScalarArrayTypeAdapter::class, 'valueTypeAdapter', $adapter);
        }
    }

    /**
     * @dataProvider enableScalarTypeAdapters
     * @param bool $enable
     */
    public function testCreateNestedTwoGenerics(bool $enable): void
    {
        $factory = new ArrayTypeAdapterFactory($enable);
        $phpType = new TypeToken('array<array<string, int>>');
        $typeAdapterProvider = MockProvider::typeAdapterProvider(null, [], null, $enable);
        $adapter = $factory->create($phpType, $typeAdapterProvider);

        self::assertInstanceOf(TypedArrayTypeAdapter::class, $adapter);
        if ($enable) {
            self::assertAttributeInstanceOf(TypedArrayTypeAdapter::class, 'valueTypeAdapter', $adapter);
        } else {
            self::assertAttributeInstanceOf(ScalarArrayTypeAdapter::class, 'valueTypeAdapter', $adapter);
        }
    }

    public function testCreateWithNonStringOrIntegerKey(): void
    {
        $factory = new ArrayTypeAdapterFactory(true);
        $phpType = new TypeToken('array<float, string>');
        $typeAdapterProvider = MockProvider::typeAdapterProvider();
        try {
            $factory->create($phpType, $typeAdapterProvider);
        } catch (LogicException $exception) {
            self::assertSame('Array keys must be strings or integers', $exception->getMessage());
            return;
        }
        self::fail('Exception was not thrown');
    }

    public function testCreateWithTooManyGenerics(): void
    {
        $factory = new ArrayTypeAdapterFactory(true);
        $phpType = new TypeToken('array<string, string, int>');
        $typeAdapterProvider = MockProvider::typeAdapterProvider();
        try {
            $factory->create($phpType, $typeAdapterProvider);
        } catch (LogicException $exception) {
            self::assertSame('Array may not have more than 2 generic types', $exception->getMessage());
            return;
        }
        self::fail('Exception was not thrown');
    }

    public function enableScalarTypeAdapters(): array
    {
        return [[true], [false]];
    }
}
