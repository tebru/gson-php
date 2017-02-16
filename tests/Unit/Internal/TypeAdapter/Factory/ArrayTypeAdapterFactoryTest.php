<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter\Factory;

use PHPUnit_Framework_TestCase;
use stdClass;
use Tebru\Gson\PhpType;
use Tebru\Gson\Internal\TypeAdapter\ArrayTypeAdapter;
use Tebru\Gson\Internal\TypeAdapter\Factory\ArrayTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapterProvider;

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

        self::assertTrue($factory->supports(new PhpType('array')));
    }

    public function testValidSupportsStdClass()
    {
        $factory = new ArrayTypeAdapterFactory();

        self::assertTrue($factory->supports(new PhpType(stdClass::class)));
    }

    public function testInvalidSupports()
    {
        $factory = new ArrayTypeAdapterFactory();

        self::assertFalse($factory->supports(new PhpType('string')));
    }

    public function testCreate()
    {
        $factory = new ArrayTypeAdapterFactory();
        $phpType = new PhpType('array');
        $typeAdapterProvider = new TypeAdapterProvider([]);
        $adapter = $factory->create($phpType, $typeAdapterProvider);

        self::assertInstanceOf(ArrayTypeAdapter::class, $adapter);
        self::assertAttributeSame($phpType, 'phpType', $adapter);
        self::assertAttributeSame($typeAdapterProvider, 'typeAdapterProvider', $adapter);
    }
}
