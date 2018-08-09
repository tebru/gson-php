<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Annotation;

use PHPUnit\Framework\TestCase;
use Tebru\Gson\Annotation\Expose;

/**
 * Class ExposeTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Annotation\Expose
 */
class ExposeTest extends TestCase
{
    public function testShouldExposeDefault(): void
    {
        $Expose = new Expose([]);

        self::assertTrue($Expose->shouldExpose(true));
        self::assertTrue($Expose->shouldExpose(false));
    }

    public function testShouldExposeSerialize(): void
    {
        $Expose = new Expose(['deserialize' => false]);

        self::assertTrue($Expose->shouldExpose(true));
        self::assertFalse($Expose->shouldExpose(false));
    }

    public function testShouldExposeDeserialize(): void
    {
        $Expose = new Expose(['serialize' => false]);

        self::assertFalse($Expose->shouldExpose(true));
        self::assertTrue($Expose->shouldExpose(false));
    }
}
