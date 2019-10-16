<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\TypeAdapter\Factory;


use PHPUnit\Framework\TestCase;
use Tebru\Gson\TypeAdapter\Factory\StringTypeAdapterFactory;
use Tebru\Gson\TypeAdapter\StringTypeAdapter;
use Tebru\Gson\Test\MockProvider;
use Tebru\PhpType\TypeToken;

/**
 * Class StringTypeAdapterFactoryTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\TypeAdapter\Factory\StringTypeAdapterFactory
 */
class StringTypeAdapterFactoryTest extends TestCase
{
    public function testValidSupports(): void
    {
        $factory = new StringTypeAdapterFactory();

        self::assertTrue($factory->supports(new TypeToken('string')));
    }

    public function testInvalidSupports(): void
    {
        $factory = new StringTypeAdapterFactory();

        self::assertFalse($factory->supports(new TypeToken('int')));
    }

    public function testCreate(): void
    {
        $factory = new StringTypeAdapterFactory();
        $adapter = $factory->create(new TypeToken('string'), MockProvider::typeAdapterProvider());

        self::assertInstanceOf(StringTypeAdapter::class, $adapter);
    }
}
