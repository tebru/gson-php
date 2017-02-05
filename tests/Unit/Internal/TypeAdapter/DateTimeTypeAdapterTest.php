<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter;

use DateTime;
use PHPUnit_Framework_TestCase;
use Tebru\Gson\Internal\PhpType;
use Tebru\Gson\Internal\TypeAdapter\DateTimeTypeAdapter;

/**
 * Class DateTimeTypeAdapterTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\TypeAdapter\DateTimeTypeAdapter
 */
class DateTimeTypeAdapterTest extends PHPUnit_Framework_TestCase
{
    public function testNull()
    {
        $adapter = new DateTimeTypeAdapter(new PhpType(DateTime::class));

        self::assertNull($adapter->readFromJson('null'));
    }

    public function testCreateDatetimeDefault()
    {
        $adapter = new DateTimeTypeAdapter(new PhpType(DateTime::class));
        $result = $adapter->readFromJson('"2016-01-02T12:23:53-06:00"');

        self::assertSame('2016-01-02T12:23:53-06:00', $result->format(DateTime::ATOM));
    }

    public function testCreateDatetimeFormat()
    {
        $type = new PhpType(DateTime::class);
        $type->setOptions(['format' => 'm/d/Y H:i:s']);
        $adapter = new DateTimeTypeAdapter($type);
        $result = $adapter->readFromJson('"1/2/2016 12:23:53"');

        self::assertSame('2016-01-02T12:23:53+00:00', $result->format(DateTime::ATOM));
    }

    public function testCreateDatetimeFormatAndTimezone()
    {
        $type = new PhpType(DateTime::class);
        $type->setOptions(['format' => 'm/d/Y H:i:s', 'timezone' => 'America/Chicago']);
        $adapter = new DateTimeTypeAdapter($type);
        $result = $adapter->readFromJson('"1/2/2016 12:23:53"');

        self::assertSame('2016-01-02T12:23:53-06:00', $result->format(DateTime::ATOM));
    }
}
