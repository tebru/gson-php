<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\Data;

use PHPUnit_Framework_TestCase;
use Tebru\Gson\Internal\AccessorStrategy\GetByPublicProperty;
use Tebru\Gson\Internal\AccessorStrategy\SetByPublicProperty;
use Tebru\Gson\Internal\Data\Property;
use Tebru\Gson\Internal\PhpType;

/**
 * Class PropertyTest
 *
 * @author Nate Brunette <n@tebru.net>
 *
 * @covers \Tebru\Gson\Internal\Data\Property
 */
class PropertyTest extends PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $realName = 'foo';
        $serializedName = 'foo_bar';
        $type = new PhpType('Foo');

        $property = new Property($realName, $serializedName, $type, new GetByPublicProperty('foo'), new SetByPublicProperty('foo'));

        self::assertSame($realName, $property->getRealName());
        self::assertSame($serializedName, $property->getSerializedName());
        self::assertSame($type, $property->getType());
    }

    public function testSetAndGet()
    {
        $mock = new class { public $foo; };
        $realName = 'foo';
        $serializedName = 'foo_bar';
        $type = new PhpType('Foo');

        $property = new Property($realName, $serializedName, $type, new GetByPublicProperty('foo'), new SetByPublicProperty('foo'));

        $property->set($mock, 'bar');
        self::assertSame('bar', $property->get($mock));
    }
}
