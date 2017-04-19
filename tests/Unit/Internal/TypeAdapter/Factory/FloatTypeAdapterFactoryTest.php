<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter\Factory;


use PHPUnit_Framework_TestCase;
use Tebru\Gson\Internal\TypeAdapter\FloatTypeAdapter;
use Tebru\Gson\Internal\TypeAdapter\Factory\FloatTypeAdapterFactory;

use Tebru\Gson\Test\MockProvider;
use Tebru\PhpType\TypeToken;

/**
 * Class FloatTypeAdapterFactoryTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\TypeAdapter\Factory\FloatTypeAdapterFactory
 */
class FloatTypeAdapterFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testValidSupports()
    {
        $factory = new FloatTypeAdapterFactory();

        self::assertTrue($factory->supports(new TypeToken('float')));
    }

    public function testInvalidSupports()
    {
        $factory = new FloatTypeAdapterFactory();

        self::assertFalse($factory->supports(new TypeToken('string')));
    }

    public function testCreate()
    {
        $factory = new FloatTypeAdapterFactory();
        $adapter = $factory->create(new TypeToken('float'), MockProvider::typeAdapterProvider());

        self::assertInstanceOf(FloatTypeAdapter::class, $adapter);
    }
}
