<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter\Factory;

use Doctrine\Common\Cache\ArrayCache;
use PHPUnit_Framework_TestCase;
use Tebru\Gson\Internal\DefaultPhpType;
use Tebru\Gson\Internal\TypeAdapter\BooleanTypeAdapter;
use Tebru\Gson\Internal\TypeAdapter\Factory\BooleanTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapterProvider;

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

        self::assertTrue($factory->supports(new DefaultPhpType('boolean')));
    }

    public function testInvalidSupports()
    {
        $factory = new BooleanTypeAdapterFactory();

        self::assertFalse($factory->supports(new DefaultPhpType('string')));
    }

    public function testCreate()
    {
        $factory = new BooleanTypeAdapterFactory();
        $adapter = $factory->create(new DefaultPhpType('boolean'), new TypeAdapterProvider([], new ArrayCache()));

        self::assertInstanceOf(BooleanTypeAdapter::class, $adapter);
    }
}
