<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\AccessorStrategy;

use PHPUnit_Framework_TestCase;
use Tebru\Gson\Internal\AccessorStrategy\GetByPublicProperty;
use Throwable;

/**
 * Class GetByPublicPropertyTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\AccessorStrategy\GetByPublicProperty
 */
class GetByPublicPropertyTest extends PHPUnit_Framework_TestCase
{
    public function testGetter()
    {
        $strategy = new GetByPublicProperty('foo');

        self::assertSame('bar', $strategy->get(new class {
            public $foo = 'bar';
        }));
    }

    public function testGetterNoProperty()
    {
        $strategy = new GetByPublicProperty('foo');

        try {
            $strategy->get(new class
            {
                public $foo2 = 'bar';
            });

            self::assertTrue(false);
        } catch (Throwable $throwable) {
            self::assertTrue(true);
        }
    }
}
