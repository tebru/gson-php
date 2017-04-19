<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Annotation;

use OutOfBoundsException;
use PHPUnit_Framework_TestCase;
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

        self::assertSame(MockSerializer::class, $type->getClass());
    }

    public function testCreateThrowsException()
    {
        try {
            new JsonAdapter([]);
        } catch (OutOfBoundsException $exception) {
            self::assertSame('@JsonAdapter annotation must specify a class as the first argument', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }
}
