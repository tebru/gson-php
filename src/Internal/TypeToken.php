<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal;

/**
 * Class TypeToken
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class TypeToken
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
     * Create a token from a string
     *
     * This is useful in combination with something like
     * gettype()
     *
     * @param string $type
     * @return string
     */
    public static function normalizeType(string $type): string
    {
        switch ($type) {
            case 'string':
                return self::STRING;
            case 'int':
            case 'integer':
                return self::INTEGER;
            case 'double':
            case 'float':
                return self::FLOAT;
            case 'bool':
            case 'boolean':
                return self::BOOLEAN;
            case 'array':
                return self::ARRAY;
            case 'null':
            case 'NULL':
                return self::NULL;
            case 'resource':
                return self::RESOURCE;
            case '?':
                return self::WILDCARD;
            default:
                return self::OBJECT;
        }
    }
}
