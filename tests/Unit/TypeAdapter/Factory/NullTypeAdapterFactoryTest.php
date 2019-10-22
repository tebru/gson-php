<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\TypeAdapter\Factory;


use PHPUnit\Framework\TestCase;
use Tebru\Gson\TypeAdapter\NullTypeAdapter;
use Tebru\Gson\Test\MockProvider;
use Tebru\PhpType\TypeToken;

/**
 * Class NullTypeAdapterFactoryTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\TypeAdapter\NullTypeAdapter
 */
class NullTypeAdapterFactoryTest extends TestCase
{
    public function testInvalidSupports(): void
    {
        $factory = new NullTypeAdapter();
        $adapter = $factory->create(new TypeToken('string'), MockProvider::typeAdapterProvider());

        self::assertNull($adapter);
    }

    public function testCreate(): void
    {
        $factory = new NullTypeAdapter();
        $adapter = $factory->create(new TypeToken('null'), MockProvider::typeAdapterProvider());

        self::assertInstanceOf(NullTypeAdapter::class, $adapter);
    }
}
