<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\TypeAdapter\Factory;


use PHPUnit\Framework\TestCase;
use Tebru\Gson\TypeAdapter\BooleanTypeAdapter;
use Tebru\Gson\Test\MockProvider;
use Tebru\PhpType\TypeToken;

/**
 * Class BooleanTypeAdapterFactoryTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\TypeAdapter\BooleanTypeAdapter
 */
class BooleanTypeAdapterFactoryTest extends TestCase
{
    public function testInvalidSupports(): void
    {
        $factory = new BooleanTypeAdapter();
        $adapter = $factory->create(new TypeToken('string'), MockProvider::typeAdapterProvider());

        self::assertNull($adapter);
    }

    public function testCreate(): void
    {
        $factory = new BooleanTypeAdapter();
        $adapter = $factory->create(new TypeToken('boolean'), MockProvider::typeAdapterProvider());

        self::assertInstanceOf(BooleanTypeAdapter::class, $adapter);
    }
}
