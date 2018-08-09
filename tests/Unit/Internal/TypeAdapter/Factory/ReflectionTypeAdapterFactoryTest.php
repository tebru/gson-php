<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter\Factory;

use PHPUnit\Framework\TestCase;
use Tebru\Gson\Internal\Excluder;
use Tebru\Gson\Internal\TypeAdapter\StringTypeAdapter;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\Internal\TypeAdapter\ReflectionTypeAdapter;
use Tebru\Gson\Internal\TypeAdapter\Factory\ReflectionTypeAdapterFactory;
use Tebru\Gson\Test\Mock\ChildClass;
use Tebru\Gson\Test\Mock\JsonAdapterClassMock;
use Tebru\Gson\Test\MockProvider;
use Tebru\PhpType\TypeToken;

/**
 * Class ReflectionTypeAdapterFactoryTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\TypeAdapter\Factory\ReflectionTypeAdapterFactory
 */
class ReflectionTypeAdapterFactoryTest extends TestCase
{
    /**
     * @var Excluder
     */
    private $excluder;

    /**
     * @var ReflectionTypeAdapterFactory
     */
    private $reflectionTypeAdapterFactory;

    /**
     * @var TypeAdapterProvider
     */
    private $typeAdapterProvider;

    public function setUp()
    {
        $this->excluder = MockProvider::excluder();
        $this->reflectionTypeAdapterFactory = MockProvider::reflectionTypeAdapterFactory($this->excluder);
        $this->typeAdapterProvider = MockProvider::typeAdapterProvider($this->excluder);
    }

    public function testValidSupports(): void
    {
        self::assertTrue($this->reflectionTypeAdapterFactory->supports(new TypeToken(ChildClass::class)));
    }

    public function testNonClassSupports(): void
    {
        self::assertFalse($this->reflectionTypeAdapterFactory->supports(new TypeToken('string')));
    }

    public function testCreate(): void
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken(ChildClass::class));

        self::assertInstanceOf(ReflectionTypeAdapter::class, $adapter);
    }

    public function testCreateJsonAdapter(): void
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken(JsonAdapterClassMock::class));

        self::assertInstanceOf(StringTypeAdapter::class, $adapter);
    }
}
