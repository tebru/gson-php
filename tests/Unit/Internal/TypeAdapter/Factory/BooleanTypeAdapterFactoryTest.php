<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter\Factory;


use PHPUnit_Framework_TestCase;
use Tebru\Gson\Internal\TypeAdapter\BooleanTypeAdapter;
use Tebru\Gson\Internal\TypeAdapter\Factory\BooleanTypeAdapterFactory;

use Tebru\Gson\Test\MockProvider;
use Tebru\PhpType\TypeToken;

/**
 * Class BooleanTypeAdapterFactoryTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\TypeAdapter\Factory\BooleanTypeAdapterFactory
 */
class BooleanTypeAdapterFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testValidSupports()
    {
        $factory = new BooleanTypeAdapterFactory();

        self::assertTrue($factory->supports(new TypeToken('boolean')));
    }

    public function testInvalidSupports()
    {
        $factory = new BooleanTypeAdapterFactory();

        self::assertFalse($factory->supports(new TypeToken('string')));
    }

    public function testCreate()
    {
        $factory = new BooleanTypeAdapterFactory();
        $adapter = $factory->create(new TypeToken('boolean'), MockProvider::typeAdapterProvider());

        self::assertInstanceOf(BooleanTypeAdapter::class, $adapter);
    }
}
