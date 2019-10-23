<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
namespace Tebru\Gson\Test\Unit\TypeAdapter\Factory;

use PHPUnit\Framework\TestCase;
use Tebru\Gson\TypeAdapter\Factory\WrappedTypeAdapterFactory;
use Tebru\Gson\Gson;
use Tebru\Gson\Test\Mock\TypeAdapterMock;
use Tebru\Gson\Test\Mock\TypeAdapterMockable;
use Tebru\Gson\Test\MockProvider;
use Tebru\PhpType\TypeToken;

/**
 * Class WrappedInterfaceTypeAdapterFactoryTest
 *
 * @author Nate Brunette <n@tebru.net>
 */
class WrappedTypeAdapterFactoryTest extends TestCase
{
    /**
     * @var WrappedTypeAdapterFactory
     */
    private $wrappedTypeAdapterFactory;

    public function setUp()
    {
        $this->wrappedTypeAdapterFactory = new WrappedTypeAdapterFactory(new TypeAdapterMock(), new TypeToken(TypeAdapterMockable::class), false);
    }

    public function testSupportsFalse(): void
    {
        self::assertNull(
            $this->wrappedTypeAdapterFactory->create(
                new TypeToken(Gson::class),
                MockProvider::typeAdapterProvider()
            )
        );
    }

    public function testSupportsStrict(): void
    {
        $wrappedTypeAdapterFactory = new WrappedTypeAdapterFactory(new TypeAdapterMock(), new TypeToken(TypeAdapterMock::class), true);
        self::assertInstanceOf(
            TypeAdapterMock::class,
            $wrappedTypeAdapterFactory->create(
                new TypeToken(TypeAdapterMock::class),
                MockProvider::typeAdapterProvider()
            )
        );
    }

    public function testIgnoresStrict(): void
    {
        $wrappedTypeAdapterFactory = new WrappedTypeAdapterFactory(new TypeAdapterMock(), new TypeToken(TypeAdapterMockable::class), true);
        self::assertNull(
            $wrappedTypeAdapterFactory->create(
                new TypeToken(TypeAdapterMock::class),
                MockProvider::typeAdapterProvider()
            )
        );
    }

    public function testSupportsFalseString(): void
    {
        self::assertNull(
            $this->wrappedTypeAdapterFactory->create(
                new TypeToken('string'),
                MockProvider::typeAdapterProvider()
            )
        );
    }

    public function testCreate(): void
    {
        self::assertInstanceOf(
            TypeAdapterMock::class,
            $this->wrappedTypeAdapterFactory->create(
                new TypeToken(TypeAdapterMock::class),
                MockProvider::typeAdapterProvider()
            )
        );
    }
}
