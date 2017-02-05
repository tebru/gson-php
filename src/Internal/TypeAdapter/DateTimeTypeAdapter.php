<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal\TypeAdapter;

use DateTime;
use DateTimeZone;
use Tebru\Gson\Internal\JsonWritable;
use Tebru\Gson\Internal\PhpType;
use Tebru\Gson\JsonReadable;
use Tebru\Gson\JsonToken;
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
    private $phpType;

    /**
     * Constructor
     *
     * @param PhpType $phpType
     */
    public function __construct(PhpType $phpType)
    {
        $this->phpType = $phpType;
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
        $format = $this->phpType->getOptions()['format'] ?? null;
        $timezone = $this->phpType->getOptions()['timezone'] ?? null;

        if (null === $format) {
            $format = DateTime::ATOM;
        }

        if (null !== $timezone) {
            $timezone = new DateTimeZone($timezone);
        }

        return DateTime::createFromFormat($format, $formattedDateTime, $timezone);
    }

    /**
     * Write the value to the writer for the type
     *
     * @param JsonWritable $writer
     * @param mixed $value
     * @return void
     */
    public function write(JsonWritable $writer, $value): void
    {
    }
}
