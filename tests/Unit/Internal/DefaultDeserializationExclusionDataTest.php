<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
namespace Tebru\Gson\Test\Unit\Internal;

use PHPUnit\Framework\TestCase;
use stdClass;
use Tebru\Gson\Context\ReaderContext;
use Tebru\Gson\Internal\DefaultDeserializationExclusionData;

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
        $context = new ReaderContext();
        $context->setPayload(json_decode('{}', true));
        $exclusionData = new DefaultDeserializationExclusionData($object, $context);
        self::assertSame([], $exclusionData->getContext()->getPayload());
        self::assertSame($object, $exclusionData->getObjectToReadInto());
        self::assertSame($context, $exclusionData->getContext());
    }
}
