<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Annotation;

use PHPUnit_Framework_TestCase;
use RuntimeException;
use Tebru\Gson\Annotation\JsonAdapter;
use Tebru\Gson\Test\Mock\MockSerializer;

/**
 * Class JsonAdapterTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Annotation\JsonAdapter
 */
class JsonAdapterTest extends PHPUnit_Framework_TestCase
{
    public function testCreateAnnotation()
    {
        $type = new JsonAdapter(['value' => MockSerializer::class]);

        self::assertSame(MockSerializer::class, $type->getValue());
    }

    public function testCreateThrowsException()
    {
        try {
            new JsonAdapter([]);
        } catch (RuntimeException $exception) {
            self::assertTrue(true);
            return;
        }
        self::assertTrue(false);
    }
}
