<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal\Naming;

use RuntimeException;
use Tebru\Gson\PropertyNamingPolicy;
use Tebru\Gson\PropertyNamingStrategy;

/**
 * Class DefaultPropertyNamingStrategy
 *
 * Converts camelCase property names to snake_case
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class DefaultPropertyNamingStrategy implements PropertyNamingStrategy
{
    /**
     * Property naming policy
     *
     * @var string
     */
    private $policy;

    /**
     * Constructor
     *
     * @param string $policy
     */
    public function __construct(string $policy)
    {
        $this->policy = $policy;
    }

    /**
     * Accepts the PHP class property name and returns the name that should
     * appear in json
     *
     * @param string $propertyName
     * @return string
     * @throws \RuntimeException
     */
    public function translateName(string $propertyName): string
    {
        switch ($this->policy) {
            case PropertyNamingPolicy::IDENTITY:
                return $propertyName;
            case PropertyNamingPolicy::LOWER_CASE_WITH_DASHES:
                return \strtolower($this->prependUpperCaseWith($propertyName, '-'));
            case PropertyNamingPolicy::LOWER_CASE_WITH_UNDERSCORES:
                return \strtolower($this->prependUpperCaseWith($propertyName, '_'));
            case PropertyNamingPolicy::UPPER_CASE_WITH_DASHES:
                return \strtoupper($this->prependUpperCaseWith($propertyName, '-'));
            case PropertyNamingPolicy::UPPER_CASE_WITH_UNDERSCORES:
                return \strtoupper($this->prependUpperCaseWith($propertyName, '_'));
            case PropertyNamingPolicy::UPPER_CAMEL_CASE:
                return \ucfirst($propertyName);
            case PropertyNamingPolicy::UPPER_CAMEL_CASE_WITH_SPACES:
                return \ucfirst($this->prependUpperCaseWith($propertyName, ' '));
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        throw new RuntimeException('Gson: This property naming strategy is not supported');
    }

    /**
     * Prepend upper case letters
     *
     * @param string $string
     * @param string $replacement
     * @return string
     */
    private function prependUpperCaseWith(string $string, string $replacement): string
    {
        return \preg_replace('/(?<!^)([A-Z])/', $replacement.'\\1', $string);
    }
}
