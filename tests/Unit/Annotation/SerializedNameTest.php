<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Annotation;

use OutOfBoundsException;
use PHPUnit_Framework_TestCase;
use Tebru\Gson\Annotation\SerializedName;

/**
 * Class SerializedNameTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Annotation\SerializedName
 */
class SerializedNameTest extends PHPUnit_Framework_TestCase
{
    public function testCreateAnnotation()
    {
        $annotation = new SerializedName(['value' => 'test']);

        self::assertSame('test', $annotation->getName());
    }

    public function testCreateThrowsException()
    {
        try {
            new SerializedName([]);
        } catch (OutOfBoundsException $exception) {
            self::assertSame('@SerializedName annotation must specify a name as the first argument', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }
}
