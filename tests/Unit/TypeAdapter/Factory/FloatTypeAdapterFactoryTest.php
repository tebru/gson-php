<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\TypeAdapter\Factory;


use PHPUnit\Framework\TestCase;
use Tebru\Gson\TypeAdapter\Factory\FloatTypeAdapterFactory;
use Tebru\Gson\TypeAdapter\FloatTypeAdapter;
use Tebru\Gson\Test\MockProvider;
use Tebru\PhpType\TypeToken;

/**
 * Class FloatTypeAdapterFactoryTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\TypeAdapter\Factory\FloatTypeAdapterFactory
 */
class FloatTypeAdapterFactoryTest extends TestCase
{
    public function testInvalidSupports(): void
    {
        $factory = new FloatTypeAdapterFactory();
        $adapter = $factory->create(new TypeToken('string'), MockProvider::typeAdapterProvider());

        self::assertNull($adapter);
    }

    public function testCreate(): void
    {
        $factory = new FloatTypeAdapterFactory();
        $adapter = $factory->create(new TypeToken('float'), MockProvider::typeAdapterProvider());

        self::assertInstanceOf(FloatTypeAdapter::class, $adapter);
    }
}
