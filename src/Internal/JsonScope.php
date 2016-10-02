<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal;

use Tebru\Enum\AbstractEnum;

/**
 * Class JsonScope
 *
 * An enum representing the state during json parsing
 *
 * @method static $this EMPTY_ARRAY()
 * @method static $this NONEMPTY_ARRAY()
 * @method static $this EMPTY_OBJECT()
 * @method static $this DANGLING_NAME()
 * @method static $this NONEMPTY_OBJECT()
 * @method static $this EMPTY_DOCUMENT()
 * @method static $this NONEMPTY_DOCUMENT()
 * @author Nate Brunette <n@tebru.net>
 */
final class JsonScope extends AbstractEnum
{
    const EMPTY_ARRAY = 0;
    const NONEMPTY_ARRAY = 1;
    const EMPTY_OBJECT = 2;
    const DANGLING_NAME = 3;
    const NONEMPTY_OBJECT = 4;
    const EMPTY_DOCUMENT = 5;
    const NONEMPTY_DOCUMENT = 6;

    /**
     * Return an array of enum class constants
     *
     * @return array
     */
    public static function getConstants(): array
    {
        return [
            self::EMPTY_ARRAY,
            self::NONEMPTY_ARRAY,
            self::EMPTY_OBJECT,
            self::DANGLING_NAME,
            self::NONEMPTY_OBJECT,
            self::EMPTY_DOCUMENT,
            self::NONEMPTY_DOCUMENT,
        ];
    }
}
