<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Annotation;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tebru\Gson\Annotation\SerializedName;

/**
 * Class SerializedNameTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Annotation\SerializedName
 */
class SerializedNameTest extends TestCase
{
    public function testCreateAnnotation(): void
    {
        $annotation = new SerializedName(['value' => 'test']);

        self::assertSame('test', $annotation->getValue());
    }

    public function testCreateThrowsException(): void
    {
        try {
            new SerializedName([]);
        } catch (RuntimeException $exception) {
            self::assertTrue(true);
            return;
        }
        self::assertTrue(false);
    }
}
