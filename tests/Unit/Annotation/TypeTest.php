<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Annotation;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;
use Tebru\Gson\Annotation\Type;

/**
 * Class TypeTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Annotation\Type
 */
class TypeTest extends TestCase
{
    public function testCreateTypeAnnotation(): void
    {
        $type = new Type(['value' => stdClass::class]);

        self::assertSame(stdClass::class, (string) $type->getType());
    }

    public function testCreateThrowsException(): void
    {
        try {
            new Type([]);
        } catch (RuntimeException $exception) {
            self::assertTrue(true);
            return;
        }
        self::assertTrue(false);
    }
}
