<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter\Factory;


use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use Tebru\Gson\Internal\TypeAdapter\WildcardTypeAdapter;
use Tebru\Gson\Internal\TypeAdapter\Factory\WildcardTypeAdapterFactory;

use Tebru\Gson\Test\MockProvider;
use Tebru\PhpType\TypeToken;

/**
 * Class WildcardTypeAdapterFactoryTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\TypeAdapter\Factory\WildcardTypeAdapterFactory
 */
class WildcardTypeAdapterFactoryTest extends TestCase
{
    public function testValidSupports(): void
    {
        $factory = new WildcardTypeAdapterFactory();

        self::assertTrue($factory->supports(new TypeToken('?')));
    }

    public function testInvalidSupports(): void
    {
        $factory = new WildcardTypeAdapterFactory();

        self::assertFalse($factory->supports(new TypeToken('string')));
    }

    public function testSupportsInterface(): void
    {
        $factory = new WildcardTypeAdapterFactory();

        self::assertTrue($factory->supports(new TypeToken(DateTimeInterface::class)));
    }

    public function testCreate(): void
    {
        $factory = new WildcardTypeAdapterFactory();
        $adapter = $factory->create(new TypeToken('?'), MockProvider::typeAdapterProvider());

        self::assertInstanceOf(WildcardTypeAdapter::class, $adapter);
    }
}
