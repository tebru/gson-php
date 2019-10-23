<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\TypeAdapter;

use DateTimeInterface;
use Tebru\Gson\Context\ReaderContext;
use Tebru\Gson\Context\WriterContext;
use Tebru\Gson\Exception\JsonSyntaxException;
use Tebru\Gson\TypeAdapter;
use Tebru\PhpType\TypeToken;

/**
 * Class DateTimeTypeAdapter
 *
 * @author Nate Brunette <n@tebru.net>
 */
class DateTimeTypeAdapter extends TypeAdapter
{
    /**
     * @var TypeToken
     */
    protected $type;

    /**
     * @var string
     */
    protected $format;

    /**
     * Constructor
     *
     * @param TypeToken $type
     * @param string $format
     */
    public function __construct(TypeToken $type, string $format)
    {
        $this->type = $type;
        $this->format = $format;
    }

    /**
     * Read the next value, convert it to its type and return it
     *
     * @param string|null $value
     * @param ReaderContext $context
     * @return DateTimeInterface|null
     */
    public function read($value, ReaderContext $context): ?DateTimeInterface
    {
        if ($value === null) {
            return null;
        }

        $class = $this->type->rawType;

        /** @noinspection PhpUndefinedMethodInspection */
        $dateTime = $class::createFromFormat($this->format, $value);

        if ($dateTime !== false) {
            return $dateTime;
        }

        throw new JsonSyntaxException(sprintf(
            'Could not create "%s" class from "%s" using format "%s"',
            $class,
            $value,
            $this->format
        ));
    }

    /**
     * Write the value to the writer for the type
     *
     * @param DateTimeInterface $value
     * @param WriterContext $context
     * @return string|null
     */
    public function write($value, WriterContext $context): ?string
    {
        return $value === null ? null : $value->format($this->format);
    }
}
