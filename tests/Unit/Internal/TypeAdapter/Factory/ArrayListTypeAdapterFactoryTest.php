<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter\Factory;

use Doctrine\Common\Cache\ArrayCache;
use PHPUnit_Framework_TestCase;
use Tebru\Collection\AbstractCollection;
use Tebru\Collection\AbstractList;
use Tebru\Collection\ArrayList;
use Tebru\Collection\CollectionInterface;
use Tebru\Collection\ListInterface;
use Tebru\Gson\PhpType;
use Tebru\Gson\Internal\TypeAdapter\ArrayListTypeAdapter;
use Tebru\Gson\Internal\TypeAdapter\Factory\ArrayListTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\Test\Mock\ChildClass;

/**
 * Class ArrayListTypeAdapterFactoryTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\TypeAdapter\Factory\ArrayListTypeAdapterFactory
 */
class ArrayListTypeAdapterFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getValidSupports
     */
    public function testValidSupports($class)
    {
        $factory = new ArrayListTypeAdapterFactory();

        self::assertTrue($factory->supports(new PhpType($class)));
    }

    public function testInvalidSupport()
    {
        $factory = new ArrayListTypeAdapterFactory();

        self::assertFalse($factory->supports(new PhpType(ChildClass::class)));
    }

    public function testNonClassSupports()
    {
        $factory = new ArrayListTypeAdapterFactory();

        self::assertFalse($factory->supports(new PhpType('string')));
    }

    public function testCreate()
    {
        $factory = new ArrayListTypeAdapterFactory();
        $phpType = new PhpType(ListInterface::class);
        $typeAdapterProvider = new TypeAdapterProvider([], new ArrayCache());
        $adapter = $factory->create($phpType, $typeAdapterProvider);

        self::assertInstanceOf(ArrayListTypeAdapter::class, $adapter);
        self::assertAttributeSame($phpType, 'phpType', $adapter);
        self::assertAttributeSame($typeAdapterProvider, 'typeAdapterProvider', $adapter);
    }

    public function getValidSupports()
    {
        return [
            ['List'],
            [ArrayList::class],
            [ListInterface::class],
            [CollectionInterface::class],
            [AbstractCollection::class],
            [AbstractList::class],
        ];
    }
}
