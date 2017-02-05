<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter\Factory;

use PHPUnit_Framework_TestCase;
use Tebru\Gson\Internal\PhpType;
use Tebru\Gson\Internal\TypeAdapter\WildcardTypeAdapter;
use Tebru\Gson\Internal\TypeAdapter\Factory\WildcardTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapterProvider;

/**
 * Class WildcardTypeAdapterFactoryTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\TypeAdapter\Factory\WildcardTypeAdapterFactory
 */
class WildcardTypeAdapterFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testValidSupports()
    {
        $factory = new WildcardTypeAdapterFactory();

        self::assertTrue($factory->supports(new PhpType('?')));
    }

    public function testInvalidSupports()
    {
        $factory = new WildcardTypeAdapterFactory();

        self::assertFalse($factory->supports(new PhpType('string')));
    }

    public function testCreate()
    {
        $factory = new WildcardTypeAdapterFactory();
        $adapter = $factory->create(new PhpType('?'), new TypeAdapterProvider([]));

        self::assertInstanceOf(WildcardTypeAdapter::class, $adapter);
    }
}
