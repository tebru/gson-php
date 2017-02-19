<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter;

use DateTime;
use PHPUnit_Framework_TestCase;
use Tebru\Gson\PhpType;
use Tebru\Gson\Internal\TypeAdapter\DateTimeTypeAdapter;
use Tebru\Gson\Test\Mock\DateTimeMock;

/**
 * Class DateTimeTypeAdapterTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\TypeAdapter\DateTimeTypeAdapter
 * @covers \Tebru\Gson\TypeAdapter
 */
class DateTimeTypeAdapterTest extends PHPUnit_Framework_TestCase
{
    public function testDeserializeNull()
    {
        $adapter = new DateTimeTypeAdapter(new PhpType(DateTime::class));

        self::assertNull($adapter->readFromJson('null'));
    }

    public function testDeserializeCreateDatetimeDefault()
    {
        $adapter = new DateTimeTypeAdapter(new PhpType(DateTime::class));
        $result = $adapter->readFromJson('"2016-01-02T12:23:53-06:00"');

        self::assertSame('2016-01-02T12:23:53-06:00', $result->format(DateTime::ATOM));
    }

    public function testDeserializeCreateDatetimeFormat()
    {
        $type = new PhpType(DateTime::class);
        $type->setOptions(['format' => 'm/d/Y H:i:s']);
        $adapter = new DateTimeTypeAdapter($type);
        $result = $adapter->readFromJson('"1/2/2016 12:23:53"');

        self::assertSame('2016-01-02T12:23:53+00:00', $result->format(DateTime::ATOM));
    }

    public function testDeserializeCreateDatetimeFormatAndTimezone()
    {
        $type = new PhpType(DateTime::class);
        $type->setOptions(['format' => 'm/d/Y H:i:s', 'timezone' => 'America/Chicago']);
        $adapter = new DateTimeTypeAdapter($type);
        $result = $adapter->readFromJson('"1/2/2016 12:23:53"');

        self::assertSame('2016-01-02T12:23:53-06:00', $result->format(DateTime::ATOM));
    }

    public function testSerializeNull()
    {
        $type = new PhpType(DateTime::class);
        $type->setOptions(['format' => 'm/d/Y H:i:s']);
        $adapter = new DateTimeTypeAdapter($type);

        self::assertSame('null', $adapter->writeToJson(null, false));
    }

    public function testSerializeDefault()
    {
        $type = new PhpType(DateTime::class);
        $adapter = new DateTimeTypeAdapter($type);

        $dateTime = DateTime::createFromFormat(DateTime::ATOM, '2016-01-02T12:23:53-06:00');

        self::assertSame('"2016-01-02T12:23:53-06:00"', $adapter->writeToJson($dateTime, false));
    }

    public function testSerializeDifferentFormat()
    {
        $type = new PhpType(DateTime::class);
        $type->setOptions(['format' => 'm/d/Y H:i:s']);
        $adapter = new DateTimeTypeAdapter($type);

        $dateTime = DateTime::createFromFormat(DateTime::ATOM, '2016-01-02T12:23:53-06:00');

        self::assertSame('"01\/02\/2016 12:23:53"', $adapter->writeToJson($dateTime, false));
    }

    public function testDeserializeCreateDatetimeFormatAndTimezoneWithSubclass()
    {
        $type = new PhpType(DateTimeMock::class);
        $type->setOptions(['format' => 'm/d/Y H:i:s', 'timezone' => 'America/Chicago']);
        $adapter = new DateTimeTypeAdapter($type);
        $result = $adapter->readFromJson('"1/2/2016 12:23:53"');

        self::assertInstanceOf(DateTimeMock::class, $result);
        self::assertSame('2016-01-02T12:23:53-06:00', $result->format(DateTime::ATOM));
    }
}
