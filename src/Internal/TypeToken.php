<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal;

use Tebru\Enum\AbstractEnum;

/**
 * Class TypeToken
 *
 * @method static $this STRING()
 * @method static $this INTEGER()
 * @method static $this FLOAT()
 * @method static $this BOOLEAN()
 * @method static $this ARRAY()
 * @method static $this OBJECT()
 * @method static $this NULL()
 * @method static $this RESOURCE()
 * @method static $this WILDCARD()
 * @author Nate Brunette <n@tebru.net>
 */
final class TypeToken extends AbstractEnum
{
    const STRING = 'string';
    const INTEGER = 'integer';
    const FLOAT = 'float';
    const BOOLEAN = 'boolean';
    const ARRAY = 'array';
    const OBJECT = 'object';
    const NULL = 'null';
    const RESOURCE = 'resource';
    const WILDCARD = '?';

    /**
     * Return an array of enum class constants
     *
     * @return array
     */
    public static function getConstants(): array
    {
        return [
            self::STRING,
            self::INTEGER,
            self::FLOAT,
            self::BOOLEAN,
            self::ARRAY,
            self::OBJECT,
            self::NULL,
            self::RESOURCE,
            self::WILDCARD,
        ];
    }

    /**
     * Create a token from a string
     *
     * This is useful in combination with something like
     * gettype()
     *
     * @param string $type
     * @return TypeToken
     * @throws \RuntimeException If the value is not valid
     */
    public static function createFromString(string $type): TypeToken
    {
        switch ($type) {
            case 'string':
                return TypeToken::create(self::STRING);
            case 'int':
            case 'integer':
                return TypeToken::create(self::INTEGER);
            case 'double':
            case 'float':
                return TypeToken::create(self::FLOAT);
            case 'bool':
            case 'boolean':
                return TypeToken::create(self::BOOLEAN);
            case 'array':
                return TypeToken::create(self::ARRAY);
            case 'null':
            case 'NULL':
                return TypeToken::create(self::NULL);
            case 'resource':
                return TypeToken::create(self::RESOURCE);
            case '?':
                return TypeToken::create(self::WILDCARD);
            default:
                return TypeToken::create(self::OBJECT);
        }
    }
}
