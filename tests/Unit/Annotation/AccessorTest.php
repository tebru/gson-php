<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Annotation;

use PHPUnit_Framework_TestCase;
use RuntimeException;
use Tebru\Gson\Annotation\Accessor;

/**
 * Class AccessorTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Annotation\Accessor
 */
class AccessorTest extends PHPUnit_Framework_TestCase
{
    public function testCreateAnnotationGetter()
    {
        $annotation = new Accessor(['get' => 'getFoo']);

        self::assertSame('getFoo', $annotation->getter());
    }

    public function testCreateAnnotationSetter()
    {
        $annotation = new Accessor(['set' => 'setFoo']);

        self::assertSame('setFoo', $annotation->setter());
    }

    public function testCreateAnnotationGetterAndSetter()
    {
        $annotation = new Accessor(['get' => 'getFoo', 'set' => 'setFoo']);

        self::assertSame('getFoo', $annotation->getter());
        self::assertSame('setFoo', $annotation->setter());
    }

    public function testCreateAnnotationThrowsException()
    {
        try {
            new Accessor([]);
        } catch (RuntimeException $exception) {
            self::assertTrue(true);
            return;
        }
        self::assertTrue(false);
    }
}
