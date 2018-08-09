<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter;

use DateTime;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Tebru\Gson\Exception\JsonSyntaxException;
use Tebru\Gson\Internal\TypeAdapter\DateTimeTypeAdapter;
use Tebru\PhpType\TypeToken;

/**
 * Class DateTimeTypeAdapterTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\TypeAdapter\DateTimeTypeAdapter
 * @covers \Tebru\Gson\TypeAdapter
 */
class DateTimeTypeAdapterTest extends TestCase
{
    public function testDeserializeNull(): void
    {
        $adapter = new DateTimeTypeAdapter(new TypeToken(DateTime::class), DateTime::ATOM);

        self::assertNull($adapter->readFromJson('null'));
    }

    public function testDeserializeCreateDatetimeDefault(): void
    {
        $adapter = new DateTimeTypeAdapter(new TypeToken(DateTime::class), DateTime::ATOM);
        $result = $adapter->readFromJson('"2016-01-02T12:23:53-06:00"');

        self::assertSame('2016-01-02T12:23:53-06:00', $result->format(DateTime::ATOM));
    }

    public function testDeserializeCreateDatetimeImmutable(): void
    {
        $adapter = new DateTimeTypeAdapter(new TypeToken(DateTimeImmutable::class), DateTime::ATOM);
        $result = $adapter->readFromJson('"2016-01-02T12:23:53-06:00"');

        self::assertSame('2016-01-02T12:23:53-06:00', $result->format(DateTime::ATOM));
    }

    public function testDeserializeException(): void
    {
        $adapter = new DateTimeTypeAdapter(new TypeToken(DateTime::class), DateTime::ATOM);
        try {
            $adapter->readFromJson('"2016-0102T12:23:53-06:00"');
        } catch (JsonSyntaxException $exception) {
            self::assertSame('Could not create "DateTime" class from "2016-0102T12:23:53-06:00" using format "Y-m-d\TH:i:sP" at "$"', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testSerializeNull(): void
    {
        $type = new TypeToken(DateTime::class);
        $adapter = new DateTimeTypeAdapter($type, DateTime::ATOM);

        self::assertSame('null', $adapter->writeToJson(null, false));
    }

    public function testSerializeDefault(): void
    {
        $type = new TypeToken(DateTime::class);
        $adapter = new DateTimeTypeAdapter($type, DateTime::ATOM);

        $dateTime = DateTime::createFromFormat(DateTime::ATOM, '2016-01-02T12:23:53-06:00');

        self::assertSame('"2016-01-02T12:23:53-06:00"', $adapter->writeToJson($dateTime, false));
    }

    public function testSerializeImmutable(): void
    {
        $type = new TypeToken(DateTime::class);
        $adapter = new DateTimeTypeAdapter($type, DateTime::ATOM);

        $dateTime = DateTimeImmutable::createFromFormat(DateTime::ATOM, '2016-01-02T12:23:53-06:00');

        self::assertSame('"2016-01-02T12:23:53-06:00"', $adapter->writeToJson($dateTime, false));
    }
}
