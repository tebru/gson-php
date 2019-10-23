<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\TypeAdapter\Factory;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tebru\Gson\ClassMetadataVisitor;
use Tebru\Gson\TypeAdapter\Factory\ReflectionTypeAdapterFactory;
use Tebru\Gson\Internal\Excluder;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\TypeAdapter\ReflectionTypeAdapter;
use Tebru\Gson\TypeAdapter\StringTypeAdapter;
use Tebru\Gson\Test\Mock\ChildClass;
use Tebru\Gson\Test\Mock\JsonAdapterClassMock;
use Tebru\Gson\Test\MockProvider;
use Tebru\PhpType\TypeToken;

/**
 * Class ReflectionTypeAdapterFactoryTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\TypeAdapter\Factory\ReflectionTypeAdapterFactory
 * @covers \Tebru\Gson\Annotation\ExclusionCheck
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

    /**
     * @var MockObject
     */
    private $visitorMock;

    public function setUp()
    {
        $this->visitorMock = $this->getMockBuilder(ClassMetadataVisitor::class)
            ->setMethods(['onLoaded'])
            ->getMock();
        $this->excluder = MockProvider::excluder();
        $this->reflectionTypeAdapterFactory = MockProvider::reflectionTypeAdapterFactory(
            $this->excluder,
            [$this->visitorMock],
            true
        );
        $this->typeAdapterProvider = MockProvider::typeAdapterProvider($this->excluder, [], $this->reflectionTypeAdapterFactory);
    }

    public function testNonClassSupports(): void
    {
        $adapter = $this->reflectionTypeAdapterFactory->create(new TypeToken('string'), $this->typeAdapterProvider);

        self::assertNull($adapter);
    }

    public function testNonExistentClassSupports(): void
    {
        $adapter = $this->reflectionTypeAdapterFactory->create(new TypeToken('Foo\Bar'), $this->typeAdapterProvider);

        self::assertNull($adapter);
    }

    public function testCreate(): void
    {
        $this->visitorMock->expects($this->once())->method('onLoaded');

        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken(ChildClass::class));

        self::assertInstanceOf(ReflectionTypeAdapter::class, $adapter);
    }

    public function testCreateWith(): void
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
