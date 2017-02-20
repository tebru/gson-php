<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal\TypeAdapter;

use DateTime;
use DateTimeZone;
use Tebru\Gson\JsonWritable;
use Tebru\Gson\JsonReadable;
use Tebru\Gson\JsonToken;
use Tebru\Gson\PhpType;
use Tebru\Gson\TypeAdapter;

/**
 * Class DateTimeTypeAdapter
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class DateTimeTypeAdapter extends TypeAdapter
{
    /**
     * @var PhpType
     */
    private $type;

    /**
     * Constructor
     *
     * @param PhpType $type
     */
    public function __construct(PhpType $type)
    {
        $this->type = $type;
    }

    /**
     * Read the next value, convert it to its type and return it
     *
     * @param JsonReadable $reader
     * @return DateTime|null
     * @throws \OutOfRangeException if the key doesn't exist
     */
    public function read(JsonReadable $reader): ?DateTime
    {
        if ($reader->peek() === JsonToken::NULL) {
            return $reader->nextNull();
        }

        $formattedDateTime = $reader->nextString();
        $format = $this->type->getOptions()['format'] ?? null;
        $timezone = $this->type->getOptions()['timezone'] ?? null;

        if (null === $format) {
            $format = DateTime::ATOM;
        }

        if (null !== $timezone) {
            $timezone = new DateTimeZone($timezone);
        }

        /** @var DateTime $class */
        $class = $this->type->getType();

        return $class::createFromFormat($format, $formattedDateTime, $timezone);
    }

    /**
     * Write the value to the writer for the type
     *
     * @param JsonWritable $writer
     * @param DateTime $value
     * @return void
     */
    public function write(JsonWritable $writer, $value): void
    {
        if (null === $value) {
            $writer->writeNull();

            return;
        }

        $format = $this->type->getOptions()['format'] ?? null;

        if (null === $format) {
            $format = DateTime::ATOM;
        }

        $dateTime = $value->format($format);
        $writer->writeString($dateTime);
    }
}
