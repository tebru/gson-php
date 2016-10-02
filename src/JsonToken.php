<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson;

use RuntimeException;
use Tebru\Enum\AbstractEnum;

/**
 * Class JsonToken
 *
 * An enum representing json types
 *
 * @method static $this BEGIN_ARRAY()
 * @method static $this END_ARRAY()
 * @method static $this BEGIN_OBJECT()
 * @method static $this END_OBJECT()
 * @method static $this END_DOCUMENT()
 * @method static $this STRING()
 * @method static $this BOOLEAN()
 * @method static $this NUMBER()
 * @method static $this NULL()
 * @method static $this NAME()
 * @author Nate Brunette <n@tebru.net>
 */
final class JsonToken extends AbstractEnum
{
    const BEGIN_ARRAY = 0;
    const END_ARRAY = 1;
    const BEGIN_OBJECT = 2;
    const END_OBJECT = 3;
    const END_DOCUMENT = 4;
    const STRING = 5;
    const BOOLEAN = 6;
    const NUMBER = 7;
    const NULL = 8;
    const NAME = 9;

    /**
     * Return an array of enum class constants
     *
     * @return array
     */
    public static function getConstants(): array
    {
        return [
            self::BEGIN_ARRAY,
            self::END_ARRAY,
            self::BEGIN_OBJECT,
            self::END_OBJECT,
            self::END_DOCUMENT,
            self::STRING,
            self::BOOLEAN,
            self::NUMBER,
            self::NULL,
            self::NAME,
        ];
    }

    /**
     * Get a user friendly name for the value of the token
     *
     * @return string
     * @throws \RuntimeException If the value is not handled
     */
    public function getTokenName(): string
    {
        switch ($this->getValue()) {
            case self::BEGIN_ARRAY:
                return 'Begin Array';
            case self::END_ARRAY:
                return 'End Array';
            case self::BEGIN_OBJECT:
                return 'Begin Object';
            case self::END_OBJECT:
                return 'End Object';
            case self::END_DOCUMENT:
                return 'End Document';
            case self::STRING:
                return 'String';
            case self::BOOLEAN:
                return 'Boolean';
            case self::NUMBER:
                return 'Number';
            case self::NULL:
                return 'Null';
            case self::NAME:
                return 'Name';
            default:
                throw new RuntimeException('Could not handle value of enum');
        }
    }
}
