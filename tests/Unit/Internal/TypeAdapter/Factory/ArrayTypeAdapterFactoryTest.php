<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter\Factory;


use PHPUnit_Framework_TestCase;
use stdClass;
use Tebru\Gson\Internal\TypeAdapter\ArrayTypeAdapter;
use Tebru\Gson\Internal\TypeAdapter\Factory\ArrayTypeAdapterFactory;

use Tebru\Gson\Test\MockProvider;
use Tebru\PhpType\TypeToken;

/**
 * Class ArrayTypeAdapterFactoryTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\TypeAdapter\Factory\ArrayTypeAdapterFactory
 */
class ArrayTypeAdapterFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testValidSupports()
    {
        $factory = new ArrayTypeAdapterFactory();

        self::assertTrue($factory->supports(new TypeToken('array')));
    }

    public function testValidSupportsStdClass()
    {
        $factory = new ArrayTypeAdapterFactory();

        self::assertTrue($factory->supports(new TypeToken(stdClass::class)));
    }

    public function testInvalidSupports()
    {
        $factory = new ArrayTypeAdapterFactory();

        self::assertFalse($factory->supports(new TypeToken('string')));
    }

    public function testCreate()
    {
        $factory = new ArrayTypeAdapterFactory();
        $phpType = new TypeToken('array');
        $typeAdapterProvider = MockProvider::typeAdapterProvider();
        $adapter = $factory->create($phpType, $typeAdapterProvider);

        self::assertInstanceOf(ArrayTypeAdapter::class, $adapter);
        self::assertAttributeSame($phpType, 'type', $adapter);
        self::assertAttributeSame($typeAdapterProvider, 'typeAdapterProvider', $adapter);
    }
}
