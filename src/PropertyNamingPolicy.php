<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson;

/**
 * Class PropertyNamingPolicy
 *
 * Represents standard strategies for converting properties names to their serialized equivalents.
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class PropertyNamingPolicy
{
    /**
     * Leave property name unchanged
     */
    public const IDENTITY = 'IDENTITY';

    /**
     * Convert camel case to lower case separated by dashes
     */
    public const LOWER_CASE_WITH_DASHES = 'LOWER_CASE_WITH_DASHES';

    /**
     * Convert camel case to lower case separated by underscores
     */
    public const LOWER_CASE_WITH_UNDERSCORES = 'LOWER_CASE_WITH_UNDERSCORES';

    /**
     * Convert camel case to upper case separated by dashes
     */
    public const UPPER_CASE_WITH_DASHES = 'UPPER_CASE_WITH_DASHES';

    /**
     * Convert camel case to upper case separated by underscores
     */
    public const UPPER_CASE_WITH_UNDERSCORES = 'UPPER_CASE_WITH_UNDERSCORES';

    /**
     * Capitalize the first letter of a camel case property
     */
    public const UPPER_CAMEL_CASE = 'UPPER_CAMEL_CASE';

    /**
     * Converts camel case to capitalized words
     */
    public const UPPER_CAMEL_CASE_WITH_SPACES = 'UPPER_CAMEL_CASE_WITH_SPACES';

    /**
     * List of all allowed policies
     *
     * @var string[]
     */
    public static $policies = [
        self::IDENTITY,
        self::LOWER_CASE_WITH_DASHES,
        self::LOWER_CASE_WITH_UNDERSCORES,
        self::UPPER_CASE_WITH_DASHES,
        self::UPPER_CASE_WITH_UNDERSCORES,
        self::UPPER_CASE_WITH_UNDERSCORES,
        self::UPPER_CAMEL_CASE,
        self::UPPER_CAMEL_CASE_WITH_SPACES,
    ];

    /**
     * Constructor
     *
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }
}
