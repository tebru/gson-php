<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\TypeAdapter\Factory;


use PHPUnit\Framework\TestCase;
use Tebru\Gson\TypeAdapter\BooleanTypeAdapter;
use Tebru\Gson\TypeAdapter\Factory\BooleanTypeAdapterFactory;
use Tebru\Gson\Test\MockProvider;
use Tebru\PhpType\TypeToken;

/**
 * Class BooleanTypeAdapterFactoryTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\TypeAdapter\Factory\BooleanTypeAdapterFactory
 */
class BooleanTypeAdapterFactoryTest extends TestCase
{
    public function testValidSupports(): void
    {
        $factory = new BooleanTypeAdapterFactory();

        self::assertTrue($factory->supports(new TypeToken('boolean')));
    }

    public function testInvalidSupports(): void
    {
        $factory = new BooleanTypeAdapterFactory();

        self::assertFalse($factory->supports(new TypeToken('string')));
    }

    public function testCreate(): void
    {
        $factory = new BooleanTypeAdapterFactory();
        $adapter = $factory->create(new TypeToken('boolean'), MockProvider::typeAdapterProvider());

        self::assertInstanceOf(BooleanTypeAdapter::class, $adapter);
    }
}
