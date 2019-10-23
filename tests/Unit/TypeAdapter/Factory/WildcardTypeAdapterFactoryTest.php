<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\TypeAdapter\Factory;


use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use Tebru\Gson\TypeAdapter\Factory\WildcardTypeAdapterFactory;
use Tebru\Gson\Test\MockProvider;
use Tebru\Gson\TypeAdapter\WildcardTypeAdapter;
use Tebru\PhpType\TypeToken;

/**
 * Class WildcardTypeAdapterFactoryTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\TypeAdapter\Factory\WildcardTypeAdapterFactory
 */
class WildcardTypeAdapterFactoryTest extends TestCase
{
    public function testInvalidSupports(): void
    {
        $factory = new WildcardTypeAdapterFactory();
        $adapter = $factory->create(new TypeToken('string'), MockProvider::typeAdapterProvider());

        self::assertNull($adapter);
    }

    public function testCreateInterface(): void
    {
        $factory = new WildcardTypeAdapterFactory();
        $adapter = $factory->create(new TypeToken('?'), MockProvider::typeAdapterProvider());

        self::assertInstanceOf(WildcardTypeAdapter::class, $adapter);
    }

    public function testCreate(): void
    {
        $factory = new WildcardTypeAdapterFactory();
        $adapter = $factory->create(new TypeToken('?'), MockProvider::typeAdapterProvider());

        self::assertInstanceOf(WildcardTypeAdapter::class, $adapter);
    }
}
