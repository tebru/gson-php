<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter\Factory;

use Doctrine\Common\Cache\ArrayCache;
use PHPUnit_Framework_TestCase;
use Tebru\Collection\AbstractMap;
use Tebru\Collection\HashMap;
use Tebru\Collection\MapInterface;
use Tebru\Gson\PhpType;
use Tebru\Gson\Internal\TypeAdapter\HashMapTypeAdapter;
use Tebru\Gson\Internal\TypeAdapter\Factory\HashMapTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\Test\Mock\ChildClass;

/**
 * Class HashMapTypeAdapterFactoryTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\TypeAdapter\Factory\HashMapTypeAdapterFactory
 */
class HashMapTypeAdapterFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getValidSupports
     */
    public function testValidSupports($class)
    {
        $factory = new HashMapTypeAdapterFactory();

        self::assertTrue($factory->supports(new PhpType($class)));
    }

    public function testInvalidSupport()
    {
        $factory = new HashMapTypeAdapterFactory();

        self::assertFalse($factory->supports(new PhpType(ChildClass::class)));
    }

    public function testNonClassSupports()
    {
        $factory = new HashMapTypeAdapterFactory();

        self::assertFalse($factory->supports(new PhpType('string')));
    }

    public function testCreate()
    {
        $factory = new HashMapTypeAdapterFactory();
        $phpType = new PhpType(MapInterface::class);
        $typeAdapterProvider = new TypeAdapterProvider([], new ArrayCache());
        $adapter = $factory->create($phpType, $typeAdapterProvider);

        self::assertInstanceOf(HashMapTypeAdapter::class, $adapter);
        self::assertAttributeSame($phpType, 'phpType', $adapter);
        self::assertAttributeSame($typeAdapterProvider, 'typeAdapterProvider', $adapter);
    }

    public function getValidSupports()
    {
        return [
            ['Map'],
            [HashMap::class],
            [MapInterface::class],
            [AbstractMap::class],
        ];
    }
}
