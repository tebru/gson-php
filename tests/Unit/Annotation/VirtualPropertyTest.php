<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Annotation;

use PHPUnit_Framework_TestCase;
use Tebru\Gson\Annotation\VirtualProperty;

/**
 * Class VirtualPropertyTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Annotation\VirtualProperty
 */
class VirtualPropertyTest extends PHPUnit_Framework_TestCase
{
    public function testCreateAnnotation()
    {
        $type = new VirtualProperty([]);

        self::assertInstanceOf(VirtualProperty::class, $type);
    }

    public function testGetAnnotationData()
    {
        $annotation = new VirtualProperty(['value' => 'foo']);

        self::assertSame('foo', $annotation->getSerializedName());
    }
}
