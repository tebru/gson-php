<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter\Factory;

use PHPUnit_Framework_TestCase;
use Tebru\Gson\Gson;
use Tebru\Gson\Internal\DefaultPhpType;
use Tebru\Gson\Internal\TypeAdapter\Factory\WrappedInterfaceTypeAdapterFactory;
use Tebru\Gson\Test\Mock\TypeAdapterMock;
use Tebru\Gson\Test\Mock\TypeAdapterMockable;
use Tebru\Gson\Test\MockProvider;

/**
 * Class WrappedInterfaceTypeAdapterFactoryTest
 *
 * @author Nate Brunette <n@tebru.net>
 */
class WrappedInterfaceTypeAdapterFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var WrappedInterfaceTypeAdapterFactory
     */
    private $wrappedTypeAdapterFactory;

    public function setUp()
    {
        $this->wrappedTypeAdapterFactory = new WrappedInterfaceTypeAdapterFactory(new TypeAdapterMock(), TypeAdapterMockable::class);
    }

    public function testSupportsTrue()
    {
        self::assertTrue($this->wrappedTypeAdapterFactory->supports(new DefaultPhpType(TypeAdapterMock::class)));
    }

    public function testSupportsFalse()
    {
        self::assertFalse($this->wrappedTypeAdapterFactory->supports(new DefaultPhpType(Gson::class)));
    }

    public function testSupportsFalseString()
    {
        self::assertFalse($this->wrappedTypeAdapterFactory->supports(new DefaultPhpType('string')));
    }

    public function testCreate()
    {
        self::assertInstanceOf(TypeAdapterMock::class, $this->wrappedTypeAdapterFactory->create(new DefaultPhpType(TypeAdapterMock::class), MockProvider::typeAdapterProvider()));
    }
}
