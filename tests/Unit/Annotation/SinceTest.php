<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Annotation;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tebru\Gson\Annotation\Since;

/**
 * Class SinceTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Annotation\Since
 */
class SinceTest extends TestCase
{
    public function testVersion(): void
    {
        $since = new Since(['value' => 1]);

        self::assertSame('1', $since->getValue());
    }

    public function testNoVersion(): void
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
