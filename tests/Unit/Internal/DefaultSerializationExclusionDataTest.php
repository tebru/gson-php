<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
namespace Tebru\Gson\Test\Unit\Internal;

use PHPUnit\Framework\TestCase;
use stdClass;
use Tebru\Gson\Context\WriterContext;
use Tebru\Gson\Internal\DefaultSerializationExclusionData;

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
        $context = new WriterContext();
        $exclusionData = new DefaultSerializationExclusionData($object, $context);
        self::assertSame($object, $exclusionData->getObjectToSerialize());
        self::assertSame($context, $exclusionData->getContext());
    }
}
