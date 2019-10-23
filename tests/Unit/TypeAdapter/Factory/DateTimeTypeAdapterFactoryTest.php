<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\TypeAdapter\Factory;

use DateTime;
use PHPUnit\Framework\TestCase;
use Tebru\Gson\TypeAdapter\DateTimeTypeAdapter;
use Tebru\Gson\TypeAdapter\Factory\DateTimeTypeAdapterFactory;
use Tebru\Gson\Test\Mock\ChildClass;
use Tebru\Gson\Test\Mock\DateTimeMock;
use Tebru\Gson\Test\MockProvider;
use Tebru\PhpType\TypeToken;

/**
 * Class DateTimeTypeAdapterFactoryTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\TypeAdapter\Factory\DateTimeTypeAdapterFactory
 */
class DateTimeTypeAdapterFactoryTest extends TestCase
{
    public function testInvalidSupports(): void
    {
        $factory = new DateTimeTypeAdapterFactory(DateTime::ATOM);
        $phpType = new TypeToken(ChildClass::class);
        $adapter = $factory->create($phpType, MockProvider::typeAdapterProvider());

        self::assertNull($adapter);
    }

    public function testNonClassSupports(): void
    {
        $factory = new DateTimeTypeAdapterFactory(DateTime::ATOM);
        $phpType = new TypeToken('string');
        $adapter = $factory->create($phpType, MockProvider::typeAdapterProvider());

        self::assertNull($adapter);
    }

    public function testCreate(): void
    {
        $factory = new DateTimeTypeAdapterFactory(DateTime::ATOM);
        $phpType = new TypeToken(DateTime::class);
        $adapter = $factory->create($phpType, MockProvider::typeAdapterProvider());

        self::assertInstanceOf(DateTimeTypeAdapter::class, $adapter);
        self::assertAttributeSame($phpType, 'type', $adapter);
    }

    public function getValidSupports(): array
    {
        return [
            [DateTime::class],
            [DateTimeMock::class],
        ];
    }
}
