<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
namespace Tebru\Gson\Test\Unit\Internal;

use PHPUnit\Framework\TestCase;
use stdClass;
use Tebru\Gson\Internal\DefaultSerializationExclusionData;
use Tebru\Gson\Internal\JsonEncodeWriter;

/**
 * Class DefaultSerializationExclusionDataTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\DefaultSerializationExclusionData
 */
class DefaultSerializationExclusionDataTest extends TestCase
{
    public function testGetters(): void
    {
        $object = new stdClass();
        $exclusionData = new DefaultSerializationExclusionData($object, new JsonEncodeWriter());
        self::assertSame($object, $exclusionData->getObjectToSerialize());
        self::assertSame('$', $exclusionData->getPath());
    }

    public function testUpdatePath(): void
    {
        $object = new stdClass();
        $writer = new JsonEncodeWriter();
        $writer->beginObject();
        $exclusionData = new DefaultSerializationExclusionData($object, $writer);
        $writer->name('foo');

        self::assertSame('$.foo', $exclusionData->getPath());
    }
}
