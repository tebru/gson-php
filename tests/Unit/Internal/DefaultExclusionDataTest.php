<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
namespace Tebru\Gson\Test\Unit\Internal;

use PHPUnit_Framework_TestCase;
use stdClass;
use Tebru\Gson\Internal\DefaultExclusionData;

/**
 * Class DefaultExclusionDataTest
 *
 * @author Nate Brunette <n@tebru.net>
 */
class DefaultExclusionDataTest extends PHPUnit_Framework_TestCase
{
    public function testIsSerializeTrue()
    {
        $exclusionData = new DefaultExclusionData(true, new stdClass());
        self::assertTrue($exclusionData->isSerialize());
    }

    public function testIsSerializeFalse()
    {
        $exclusionData = new DefaultExclusionData(false, new stdClass());
        self::assertFalse($exclusionData->isSerialize());
    }

    public function testGetData()
    {
        $data = new stdClass();
        $data->foo = 'bar';

        $exclusionData = new DefaultExclusionData(false, $data);
        self::assertSame($data, $exclusionData->getData());
    }

    public function testGetPayload()
    {
        $data = new stdClass();
        $data->foo = 'bar';
        $payload = ['foo', 'bar2'];

        $exclusionData = new DefaultExclusionData(false, $data, $payload);
        self::assertSame($payload, $exclusionData->getDeserializePayload());
    }
}
