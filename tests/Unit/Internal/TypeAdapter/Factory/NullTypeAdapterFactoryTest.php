<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter\Factory;

use PHPUnit_Framework_TestCase;
use Tebru\Gson\PhpType;
use Tebru\Gson\Internal\TypeAdapter\NullTypeAdapter;
use Tebru\Gson\Internal\TypeAdapter\Factory\NullTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapterProvider;

/**
 * Class NullTypeAdapterFactoryTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\TypeAdapter\Factory\NullTypeAdapterFactory
 */
class NullTypeAdapterFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testValidSupports()
    {
        $factory = new NullTypeAdapterFactory();

        self::assertTrue($factory->supports(new PhpType('null')));
    }

    public function testInvalidSupports()
    {
        $factory = new NullTypeAdapterFactory();

        self::assertFalse($factory->supports(new PhpType('string')));
    }

    public function testCreate()
    {
        $factory = new NullTypeAdapterFactory();
        $adapter = $factory->create(new PhpType('null'), new TypeAdapterProvider([]));

        self::assertInstanceOf(NullTypeAdapter::class, $adapter);
    }
}
