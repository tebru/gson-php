<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter\Factory;

use PHPUnit_Framework_TestCase;
use Tebru\Gson\Internal\Excluder;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\Internal\TypeAdapter\ReflectionTypeAdapter;
use Tebru\Gson\Internal\TypeAdapter\Factory\ReflectionTypeAdapterFactory;
use Tebru\Gson\Test\Mock\ChildClass;
use Tebru\Gson\Test\MockProvider;
use Tebru\PhpType\TypeToken;

/**
 * Class ReflectionTypeAdapterFactoryTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\TypeAdapter\Factory\ReflectionTypeAdapterFactory
 */
class ReflectionTypeAdapterFactoryTest extends PHPUnit_Framework_TestCase
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

    public function testValidSupports()
    {
        self::assertTrue($this->reflectionTypeAdapterFactory->supports(new TypeToken(ChildClass::class)));
    }

    public function testNonClassSupports()
    {
        self::assertFalse($this->reflectionTypeAdapterFactory->supports(new TypeToken('string')));
    }

    public function testCreate()
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken(ChildClass::class));

        self::assertInstanceOf(ReflectionTypeAdapter::class, $adapter);
    }
}
