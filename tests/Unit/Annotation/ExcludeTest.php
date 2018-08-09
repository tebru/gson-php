<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Annotation;

use PHPUnit\Framework\TestCase;
use Tebru\Gson\Annotation\Exclude;

/**
 * Class ExcludeTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Annotation\Exclude
 */
class ExcludeTest extends TestCase
{
    public function testShouldExcludeDefault(): void
    {
        $exclude = new Exclude([]);

        self::assertTrue($exclude->shouldExclude(true));
        self::assertTrue($exclude->shouldExclude(false));
    }

    public function testShouldExcludeSerialize(): void
    {
        $exclude = new Exclude(['deserialize' => false]);

        self::assertTrue($exclude->shouldExclude(true));
        self::assertFalse($exclude->shouldExclude(false));
    }

    public function testShouldExcludeDeserialize(): void
    {
        $exclude = new Exclude(['serialize' => false]);

        self::assertFalse($exclude->shouldExclude(true));
        self::assertTrue($exclude->shouldExclude(false));
    }
}
