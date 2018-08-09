<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Annotation;

use PHPUnit\Framework\TestCase;
use Tebru\Gson\Annotation\VirtualProperty;

/**
 * Class VirtualPropertyTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Annotation\VirtualProperty
 */
class VirtualPropertyTest extends TestCase
{
    public function testCreateAnnotation(): void
    {
        $type = new VirtualProperty([]);

        self::assertInstanceOf(VirtualProperty::class, $type);
    }

    public function testGetAnnotationData(): void
    {
        $annotation = new VirtualProperty(['value' => 'foo']);

        self::assertSame('foo', $annotation->getSerializedName());
    }
}
