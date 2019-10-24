<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\TypeAdapter\Factory;


use PHPUnit\Framework\TestCase;
use Tebru\Gson\TypeAdapter\ArrayTypeAdapter;
use Tebru\Gson\TypeAdapter\Factory\ArrayTypeAdapterFactory;
use Tebru\Gson\TypeAdapter\IntegerTypeAdapter;
use Tebru\Gson\Test\MockProvider;
use Tebru\Gson\TypeAdapter\ScalarArrayTypeAdapter;
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

        self::assertAttributeSame($typeAdapterProvider, 'typeAdapterProvider', $adapter);
        self::assertAttributeSame(TypeToken::create(TypeToken::WILDCARD), 'keyType', $adapter);
        self::assertAttributeEquals(new WildcardTypeAdapter($typeAdapterProvider), 'valueTypeAdapter', $adapter);
        self::assertAttributeSame(0, 'numberOfGenerics', $adapter);
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

        self::assertAttributeSame($typeAdapterProvider, 'typeAdapterProvider', $adapter);
        self::assertAttributeSame(TypeToken::create(TypeToken::WILDCARD), 'keyType', $adapter);
        self::assertAttributeEquals(new WildcardTypeAdapter($typeAdapterProvider), 'valueTypeAdapter', $adapter);
        self::assertAttributeSame(0, 'numberOfGenerics', $adapter);
    }

    public function testCreateOneGenericType(): void
    {
        $factory = new ArrayTypeAdapterFactory(true);
        $phpType = new TypeToken('array<int>');
        $typeAdapterProvider = MockProvider::typeAdapterProvider();
        $adapter = $factory->create($phpType, $typeAdapterProvider);

        self::assertAttributeSame($typeAdapterProvider, 'typeAdapterProvider', $adapter);
        self::assertAttributeSame(TypeToken::create(TypeToken::WILDCARD), 'keyType', $adapter);
        self::assertAttributeEquals(new IntegerTypeAdapter(), 'valueTypeAdapter', $adapter);
        self::assertAttributeSame(1, 'numberOfGenerics', $adapter);
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

        self::assertAttributeSame(TypeToken::create('string'), 'keyType', $adapter);
        self::assertAttributeEquals(new IntegerTypeAdapter(), 'valueTypeAdapter', $adapter);
        self::assertAttributeSame(2, 'numberOfGenerics', $adapter);
    }

    public function testCreateTwoGenericTypesWithoutScalarTypeAdapters(): void
    {
        $factory = new ArrayTypeAdapterFactory(false);
        $phpType = new TypeToken('array<string, int>');
        $typeAdapterProvider = MockProvider::typeAdapterProvider(null, [], null, false);
        $adapter = $factory->create($phpType, $typeAdapterProvider);

        self::assertAttributeSame(TypeToken::create('string'), 'keyType', $adapter);
        self::assertAttributeInstanceOf(WildcardTypeAdapter::class, 'valueTypeAdapter', $adapter);
        self::assertAttributeSame(2, 'numberOfGenerics', $adapter);
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

        self::assertAttributeSame(TypeToken::create('?'), 'keyType', $adapter);
        self::assertAttributeInstanceOf(ArrayTypeAdapter::class, 'valueTypeAdapter', $adapter);
        self::assertAttributeSame(1, 'numberOfGenerics', $adapter);
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

        self::assertAttributeSame(TypeToken::create('?'), 'keyType', $adapter);
        self::assertAttributeInstanceOf(ScalarArrayTypeAdapter::class, 'valueTypeAdapter', $adapter);
        self::assertAttributeSame(1, 'numberOfGenerics', $adapter);
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

        self::assertAttributeSame(TypeToken::create('?'), 'keyType', $adapter);
        self::assertAttributeInstanceOf(ArrayTypeAdapter::class, 'valueTypeAdapter', $adapter);
        self::assertAttributeSame(1, 'numberOfGenerics', $adapter);
    }

    public function enableScalarTypeAdapters(): array
    {
        return [[true], [false]];
    }
}
