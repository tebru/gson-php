<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\AccessorStrategy;

use PHPUnit_Framework_TestCase;
use Tebru\Gson\Internal\AccessorStrategy\GetByMethod;
use Throwable;

/**
 * Class GetByMethodTest
 *
 * @author Nate Brunette <n@tebru.net>
 */
class GetByMethodTest extends PHPUnit_Framework_TestCase
{
    public function testGetter()
    {
        $strategy = new GetByMethod('foo');

        self::assertSame('bar', $strategy->get(new class {
            public function foo() {
                return 'bar';
            }
        }));
    }

    public function testGetterNoMethod()
    {
        $this->expectException(Throwable::class);

        $strategy = new GetByMethod('foo');

        $strategy->get(new class {
            public function getFoo() {
                return 'bar';
            }
        });
    }
}
