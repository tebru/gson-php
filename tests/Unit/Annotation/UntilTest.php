<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Annotation;

use OutOfBoundsException;
use PHPUnit_Framework_TestCase;
use Tebru\Gson\Annotation\Until;

/**
 * Class UntilTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Annotation\Until
 */
class UntilTest extends PHPUnit_Framework_TestCase
{
    public function testVersion()
    {
        $until = new Until(['value' => 1]);

        self::assertSame('1', $until->getVersion());
    }

    public function testNoVersion()
    {
        try {
            new Until([]);
        } catch (OutOfBoundsException $exception) {
            self::assertSame('@Until annotation must specify a version as the first argument', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }
}
