<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Annotation;

use OutOfBoundsException;

/**
 * Class Since
 *
 * Used to exclude classes or properties that should not be serialized if the version number
 * is less than the @Since value.  Will not be used if version number is not defined on the
 * builder.
 *
 * @author Nate Brunette <n@tebru.net>
 *
 * @Annotation
 * @Target({"CLASS", "PROPERTY", "METHOD"})
 */
class Since
{
    /**
     * @var string
     */
    private $value;

    /**
     * Constructor
     *
     * @param array $params
     * @throws \OutOfBoundsException
     */
    public function __construct(array $params)
    {
        if (!isset($params['value'])) {
            throw new OutOfBoundsException('@Since annotation must specify a version as the first argument');
        }

        $this->value = (string) $params['value'];
    }

    /**
     * Returns the version
     *
     * @return string
     */
    public function getVersion(): string
    {
        return $this->value;
    }
}
