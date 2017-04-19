<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter\Factory;


use PHPUnit_Framework_TestCase;
use Tebru\Gson\Internal\TypeAdapter\StringTypeAdapter;
use Tebru\Gson\Internal\TypeAdapter\Factory\StringTypeAdapterFactory;

use Tebru\Gson\Test\MockProvider;
use Tebru\PhpType\TypeToken;

/**
 * Class StringTypeAdapterFactoryTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\TypeAdapter\Factory\StringTypeAdapterFactory
 */
class StringTypeAdapterFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testValidSupports()
    {
        $factory = new StringTypeAdapterFactory();

        self::assertTrue($factory->supports(new TypeToken('string')));
    }

    public function testInvalidSupports()
    {
        $factory = new StringTypeAdapterFactory();

        self::assertFalse($factory->supports(new TypeToken('int')));
    }

    public function testCreate()
    {
        $factory = new StringTypeAdapterFactory();
        $adapter = $factory->create(new TypeToken('string'), MockProvider::typeAdapterProvider());

        self::assertInstanceOf(StringTypeAdapter::class, $adapter);
    }
}
