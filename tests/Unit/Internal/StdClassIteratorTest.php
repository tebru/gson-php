<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal;

use PHPUnit_Framework_TestCase;
use stdClass;
use Tebru\Gson\Internal\StdClassIterator;

/**
 * Class StdClassIteratorTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\StdClassIterator
 * @covers \Tebru\Gson\Internal\AbstractIterator
 */
class StdClassIteratorTest extends PHPUnit_Framework_TestCase
{
    public function testStartsAtBeginning()
    {
        $class = new stdClass();
        $class->foo = 1;
        $class->bar = 2;

        $iterator = new StdClassIterator($class);

        self::assertSame('foo', $iterator->key());
        self::assertSame(1, $iterator->current());
    }

    public function testNext()
    {
        $class = new stdClass();
        $class->foo = 1;
        $class->bar = 2;

        $iterator = new StdClassIterator($class);
        $iterator->next();

        self::assertSame('bar', $iterator->key());
        self::assertSame(2, $iterator->current());
    }

    public function testKey()
    {
        $class = new stdClass();
        $class->foo = 1;
        $class->bar = 2;

        $iterator = new StdClassIterator($class);
        $iterator->next();

        self::assertSame('bar', $iterator->key());
    }

    public function testValid()
    {
        $class = new stdClass();
        $class->foo = 1;
        $class->bar = 2;

        $iterator = new StdClassIterator($class);
        $iterator->next();

        self::assertTrue($iterator->valid());
    }

    public function testValidFalse()
    {
        $class = new stdClass();
        $class->foo = 1;
        $class->bar = 2;

        $iterator = new StdClassIterator($class);
        $iterator->next();
        $iterator->next();

        self::assertFalse($iterator->valid());
    }

    public function testValidBeginning()
    {
        $class = new stdClass();
        $class->foo = 1;
        $class->bar = 2;

        $iterator = new StdClassIterator($class);

        self::assertTrue($iterator->valid());
    }

    public function testRewind()
    {
        $class = new stdClass();
        $class->foo = 1;
        $class->bar = 2;

        $iterator = new StdClassIterator($class);
        $iterator->next();
        $iterator->next();
        $iterator->rewind();

        self::assertSame('foo', $iterator->key());
    }
}
