<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter\Factory;

use DateTime;
use Doctrine\Common\Cache\ArrayCache;
use PHPUnit_Framework_TestCase;
use Tebru\Gson\Internal\DefaultPhpType;
use Tebru\Gson\Internal\TypeAdapter\DateTimeTypeAdapter;
use Tebru\Gson\Internal\TypeAdapter\Factory\DateTimeTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\Test\Mock\ChildClass;
use Tebru\Gson\Test\Mock\DateTimeMock;

/**
 * Class DateTimeTypeAdapterFactoryTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\TypeAdapter\Factory\DateTimeTypeAdapterFactory
 */
class DateTimeTypeAdapterFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getValidSupports
     */
    public function testValidSupports($class)
    {
        $factory = new DateTimeTypeAdapterFactory();

        self::assertTrue($factory->supports(new DefaultPhpType($class)));
    }

    public function testInvalidSupports()
    {
        $factory = new DateTimeTypeAdapterFactory();

        self::assertFalse($factory->supports(new DefaultPhpType(ChildClass::class)));
    }

    public function testNonClassSupports()
    {
        $factory = new DateTimeTypeAdapterFactory();

        self::assertFalse($factory->supports(new DefaultPhpType('string')));
    }

    public function testCreate()
    {
        $factory = new DateTimeTypeAdapterFactory();
        $phpType = new DefaultPhpType(DateTime::class);
        $adapter = $factory->create($phpType, new TypeAdapterProvider([], new ArrayCache()));

        self::assertInstanceOf(DateTimeTypeAdapter::class, $adapter);
        self::assertAttributeSame($phpType, 'type', $adapter);
    }

    public function getValidSupports()
    {
        return [
            [DateTime::class],
            [DateTimeMock::class],
        ];
    }
}
