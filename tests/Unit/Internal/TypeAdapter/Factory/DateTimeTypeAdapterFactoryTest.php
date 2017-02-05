<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter\Factory;

use DateTime;
use PHPUnit_Framework_TestCase;
use Tebru\Gson\Internal\PhpType;
use Tebru\Gson\Internal\TypeAdapter\DateTimeTypeAdapter;
use Tebru\Gson\Internal\TypeAdapter\Factory\DateTimeTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\Test\Mock\ChildClass;

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

        self::assertTrue($factory->supports(new PhpType($class)));
    }

    public function testInvalidSupports()
    {
        $factory = new DateTimeTypeAdapterFactory();

        self::assertFalse($factory->supports(new PhpType(ChildClass::class)));
    }

    public function testNonClassSupports()
    {
        $factory = new DateTimeTypeAdapterFactory();

        self::assertFalse($factory->supports(new PhpType('string')));
    }

    public function testCreate()
    {
        $factory = new DateTimeTypeAdapterFactory();
        $phpType = new PhpType('DateTime');
        $adapter = $factory->create($phpType, new TypeAdapterProvider([]));

        self::assertInstanceOf(DateTimeTypeAdapter::class, $adapter);
        self::assertAttributeSame($phpType, 'phpType', $adapter);
    }

    public function getValidSupports()
    {
        return [
            ['DateTime'],
            [DateTime::class],
        ];
    }
}
