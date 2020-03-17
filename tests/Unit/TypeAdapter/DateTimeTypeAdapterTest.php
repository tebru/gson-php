<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\TypeAdapter;

use DateTime;
use DateTimeImmutable;
use Tebru\Gson\TypeAdapter\DateTimeTypeAdapter;
use Tebru\Gson\Exception\JsonSyntaxException;
use Tebru\PhpType\TypeToken;

/**
 * Class DateTimeTypeAdapterTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\TypeAdapter\DateTimeTypeAdapter
 * @covers \Tebru\Gson\TypeAdapter
 */
class DateTimeTypeAdapterTest extends TypeAdapterTestCase
{
    public function testDeserializeNull(): void
    {
        $adapter = new DateTimeTypeAdapter(new TypeToken(DateTime::class));

        self::assertNull($adapter->read(json_decode('null', true), $this->readerContext));
    }

    public function testDeserializeCreateDatetimeDefault(): void
    {
        $adapter = new DateTimeTypeAdapter(new TypeToken(DateTime::class));
        $result = $adapter->read(json_decode('"2016-01-02T12:23:53-06:00"', true), $this->readerContext);

        self::assertSame('2016-01-02T12:23:53-06:00', $result->format(DateTime::ATOM));
    }

    public function testDeserializeCreateDatetimeImmutable(): void
    {
        $adapter = new DateTimeTypeAdapter(new TypeToken(DateTimeImmutable::class));
        $result = $adapter->read(json_decode('"2016-01-02T12:23:53-06:00"', true), $this->readerContext);

        self::assertSame('2016-01-02T12:23:53-06:00', $result->format(DateTime::ATOM));
    }

    public function testDeserializeException(): void
    {
        $adapter = new DateTimeTypeAdapter(new TypeToken(DateTime::class));
        try {
            $adapter->read(json_decode('"2016-0102T12:23:53-06:00"', true), $this->readerContext);
        } catch (JsonSyntaxException $exception) {
            self::assertSame('Could not create "DateTime" class from "2016-0102T12:23:53-06:00" using format "Y-m-d\TH:i:sP"', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testSerializeNull(): void
    {
        $type = new TypeToken(DateTime::class);
        $adapter = new DateTimeTypeAdapter($type);

        self::assertNull($adapter->write(null, $this->writerContext));
    }

    public function testSerializeDefault(): void
    {
        $type = new TypeToken(DateTime::class);
        $adapter = new DateTimeTypeAdapter($type);

        $dateTime = DateTime::createFromFormat(DateTime::ATOM, '2016-01-02T12:23:53-06:00');

        self::assertSame('2016-01-02T12:23:53-06:00', $adapter->write($dateTime, $this->writerContext));
    }

    public function testSerializeImmutable(): void
    {
        $type = new TypeToken(DateTime::class);
        $adapter = new DateTimeTypeAdapter($type);

        $dateTime = DateTimeImmutable::createFromFormat(DateTime::ATOM, '2016-01-02T12:23:53-06:00');

        self::assertSame('2016-01-02T12:23:53-06:00', $adapter->write($dateTime, $this->writerContext));
    }
}
