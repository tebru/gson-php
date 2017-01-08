<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\Data;

use PHPUnit_Framework_TestCase;
use Tebru\Gson\Internal\Data\ClassNameSet;

/**
 * Class ClassNameSetTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\Data\ClassNameSet
 */
class ClassNameSetTest extends PHPUnit_Framework_TestCase
{
    public function testGetKeyUsesClass()
    {
        $set = new ClassNameSet();

        self::assertSame(ClassNameSetTest::class, $set->getKey($this));
    }
}
