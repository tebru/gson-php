<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Annotation;

use OutOfBoundsException;
use PHPUnit_Framework_TestCase;
use Tebru\Gson\Annotation\Since;

/**
 * Class SinceTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Annotation\Since
 */
class SinceTest extends PHPUnit_Framework_TestCase
{
    public function testVersion()
    {
        $since = new Since(['value' => 1]);

        self::assertSame('1', $since->getVersion());
    }

    public function testNoVersion()
    {
        try {
            new Since([]);
        } catch (OutOfBoundsException $exception) {
            self::assertSame('@Since annotation must specify a version as the first argument', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }
}
