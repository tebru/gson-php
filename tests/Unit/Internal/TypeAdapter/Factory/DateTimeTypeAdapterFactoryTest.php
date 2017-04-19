<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter\Factory;

use DateTime;

use PHPUnit_Framework_TestCase;
use Tebru\Gson\Internal\TypeAdapter\DateTimeTypeAdapter;
use Tebru\Gson\Internal\TypeAdapter\Factory\DateTimeTypeAdapterFactory;

use Tebru\Gson\Test\Mock\ChildClass;
use Tebru\Gson\Test\Mock\DateTimeMock;
use Tebru\Gson\Test\MockProvider;
use Tebru\PhpType\TypeToken;

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
        $factory = new DateTimeTypeAdapterFactory(DateTime::ATOM);

        self::assertTrue($factory->supports(new TypeToken($class)));
    }

    public function testInvalidSupports()
    {
        $factory = new DateTimeTypeAdapterFactory(DateTime::ATOM);

        self::assertFalse($factory->supports(new TypeToken(ChildClass::class)));
    }

    public function testNonClassSupports()
    {
        $factory = new DateTimeTypeAdapterFactory(DateTime::ATOM);

        self::assertFalse($factory->supports(new TypeToken('string')));
    }

    public function testCreate()
    {
        $factory = new DateTimeTypeAdapterFactory(DateTime::ATOM);
        $phpType = new TypeToken(DateTime::class);
        $adapter = $factory->create($phpType, MockProvider::typeAdapterProvider());

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
