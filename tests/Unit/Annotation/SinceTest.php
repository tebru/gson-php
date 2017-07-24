<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Annotation;

use PHPUnit_Framework_TestCase;
use RuntimeException;
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

        self::assertSame('1', $since->getValue());
    }

    public function testNoVersion()
    {
        try {
            new Since([]);
        } catch (RuntimeException $exception) {
            self::assertTrue(true);
            return;
        }
        self::assertTrue(false);
    }
}
