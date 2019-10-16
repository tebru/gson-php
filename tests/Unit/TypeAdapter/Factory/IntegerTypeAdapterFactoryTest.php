<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\TypeAdapter\Factory;


use PHPUnit\Framework\TestCase;
use Tebru\Gson\TypeAdapter\Factory\IntegerTypeAdapterFactory;
use Tebru\Gson\TypeAdapter\IntegerTypeAdapter;
use Tebru\Gson\Test\MockProvider;
use Tebru\PhpType\TypeToken;

/**
 * Class IntegerTypeAdapterFactoryTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\TypeAdapter\Factory\IntegerTypeAdapterFactory
 */
class IntegerTypeAdapterFactoryTest extends TestCase
{
    public function testInvalidSupports(): void
    {
        $factory = new IntegerTypeAdapterFactory();
        $adapter = $factory->create(new TypeToken('string'), MockProvider::typeAdapterProvider());

        self::assertNull($adapter);
    }

    public function testCreate(): void
    {
        $factory = new IntegerTypeAdapterFactory();
        $adapter = $factory->create(new TypeToken('int'), MockProvider::typeAdapterProvider());

        self::assertInstanceOf(IntegerTypeAdapter::class, $adapter);
    }
}
