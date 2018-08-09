<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
namespace Tebru\Gson\Test\Unit\Internal;

use PHPUnit\Framework\TestCase;
use stdClass;
use Tebru\Gson\Internal\DefaultDeserializationExclusionData;
use Tebru\Gson\Internal\DefaultReaderContext;
use Tebru\Gson\Internal\JsonDecodeReader;

/**
 * Class DefaultDeserializationExclusionDataTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\DefaultDeserializationExclusionData
 */
class DefaultDeserializationExclusionDataTest extends TestCase
{
    public function testGetters(): void
    {
        $object = new stdClass();
        $context = new DefaultReaderContext();
        $exclusionData = new DefaultDeserializationExclusionData($object, new JsonDecodeReader('{}', $context));
        self::assertInstanceOf(stdClass::class, $exclusionData->getPayload());
        self::assertSame($object, $exclusionData->getObjectToReadInto());
        self::assertSame($context, $exclusionData->getContext());
        self::assertSame('$', $exclusionData->getPath());
    }

    public function testUpdatePath(): void
    {
        $object = new stdClass();
        $context = new DefaultReaderContext();
        $reader = new JsonDecodeReader('{"foo": "bar"}', $context);
        $reader->beginObject();
        $exclusionData = new DefaultDeserializationExclusionData($object, $reader);
        $reader->nextName();

        self::assertSame('$.foo', $exclusionData->getPath());
    }
}
