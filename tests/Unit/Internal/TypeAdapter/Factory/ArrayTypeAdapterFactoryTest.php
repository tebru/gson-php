<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter\Factory;


use PHPUnit\Framework\TestCase;
use stdClass;
use Tebru\Gson\Internal\TypeAdapter\Factory\ArrayTypeAdapterFactory;

use Tebru\Gson\Internal\TypeAdapter\IntegerTypeAdapter;
use Tebru\Gson\Internal\TypeAdapter\WildcardTypeAdapter;
use Tebru\Gson\Test\MockProvider;
use Tebru\PhpType\TypeToken;

/**
 * Class ArrayTypeAdapterFactoryTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\TypeAdapter\Factory\ArrayTypeAdapterFactory
 */
class ArrayTypeAdapterFactoryTest extends TestCase
{
    public function testValidSupports(): void
    {
        $factory = new ArrayTypeAdapterFactory();

        self::assertTrue($factory->supports(new TypeToken('array')));
    }

    public function testValidSupportsStdClass(): void
    {
        $factory = new ArrayTypeAdapterFactory();

        self::assertTrue($factory->supports(new TypeToken(stdClass::class)));
    }

    public function testInvalidSupports(): void
    {
        $factory = new ArrayTypeAdapterFactory();

        self::assertFalse($factory->supports(new TypeToken('string')));
    }

    public function testCreate(): void
    {
        $factory = new ArrayTypeAdapterFactory();
        $phpType = new TypeToken('array');
        $typeAdapterProvider = MockProvider::typeAdapterProvider();
        $adapter = $factory->create($phpType, $typeAdapterProvider);

        self::assertAttributeSame($typeAdapterProvider, 'typeAdapterProvider', $adapter);
        self::assertAttributeSame(TypeToken::create(TypeToken::WILDCARD), 'keyType', $adapter);
        self::assertAttributeEquals(new WildcardTypeAdapter($typeAdapterProvider), 'valueTypeAdapter', $adapter);
        self::assertAttributeSame(0, 'numberOfGenerics', $adapter);
    }

    public function testCreateOneGenericType(): void
    {
        $factory = new ArrayTypeAdapterFactory();
        $phpType = new TypeToken('array<int>');
        $typeAdapterProvider = MockProvider::typeAdapterProvider();
        $adapter = $factory->create($phpType, $typeAdapterProvider);

        self::assertAttributeSame($typeAdapterProvider, 'typeAdapterProvider', $adapter);
        self::assertAttributeSame(TypeToken::create(TypeToken::WILDCARD), 'keyType', $adapter);
        self::assertAttributeEquals(new IntegerTypeAdapter(), 'valueTypeAdapter', $adapter);
        self::assertAttributeSame(1, 'numberOfGenerics', $adapter);
    }

    public function testCreateTwoGenericTypes(): void
    {
        $factory = new ArrayTypeAdapterFactory();
        $phpType = new TypeToken('array<string, int>');
        $typeAdapterProvider = MockProvider::typeAdapterProvider();
        $adapter = $factory->create($phpType, $typeAdapterProvider);

        self::assertAttributeSame($typeAdapterProvider, 'typeAdapterProvider', $adapter);
        self::assertAttributeSame(TypeToken::create('string'), 'keyType', $adapter);
        self::assertAttributeEquals(new IntegerTypeAdapter(), 'valueTypeAdapter', $adapter);
        self::assertAttributeSame(2, 'numberOfGenerics', $adapter);
    }
}
