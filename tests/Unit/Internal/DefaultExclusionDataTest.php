<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
namespace Tebru\Gson\Test\Unit\Internal;

use PHPUnit\Framework\TestCase;
use stdClass;
use Tebru\Gson\Internal\DefaultExclusionData;

/**
 * Class DefaultExclusionDataTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\DefaultExclusionData
 */
class DefaultExclusionDataTest extends TestCase
{
    public function testIsSerializeTrue(): void
    {
        $exclusionData = new DefaultExclusionData(true, new stdClass());
        self::assertTrue($exclusionData->isSerialize());
    }

    public function testIsSerializeFalse(): void
    {
        $exclusionData = new DefaultExclusionData(false, new stdClass());
        self::assertFalse($exclusionData->isSerialize());
    }

    public function testGetData(): void
    {
        $data = new stdClass();
        $data->foo = 'bar';

        $exclusionData = new DefaultExclusionData(false, $data);
        self::assertSame($data, $exclusionData->getData());
    }

    public function testGetPayload(): void
    {
        $data = new stdClass();
        $data->foo = 'bar';
        $payload = ['foo', 'bar2'];

        $exclusionData = new DefaultExclusionData(false, $data, $payload);
        self::assertSame($payload, $exclusionData->getDeserializePayload());
    }
}
