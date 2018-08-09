<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Annotation;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tebru\Gson\Annotation\Until;

/**
 * Class UntilTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Annotation\Until
 */
class UntilTest extends TestCase
{
    public function testVersion(): void
    {
        $until = new Until(['value' => 1]);

        self::assertSame('1', $until->getValue());
    }

    public function testNoVersion(): void
    {
        try {
            new Until([]);
        } catch (RuntimeException $exception) {
            self::assertTrue(true);
            return;
        }
        self::assertTrue(false);
    }
}
