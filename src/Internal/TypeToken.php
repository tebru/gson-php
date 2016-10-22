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
    public static function getConstants()
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
     * @return static
     */
    public static function createFromString(string $type)
    {
        switch ($type) {
            case 'string':
                return new static(self::STRING);
            case 'int':
            case 'integer':
                return new static(self::INTEGER);
            case 'double':
            case 'float':
                return new static(self::FLOAT);
            case 'bool':
            case 'boolean':
                return new static(self::BOOLEAN);
            case 'array':
                return new static(self::ARRAY);
            case 'null':
            case 'NULL':
                return new static(self::NULL);
            case 'resource':
                return new static(self::RESOURCE);
            case '?':
                return new static(self::WILDCARD);
            default:
                return new static(self::OBJECT);
        }
    }
}
