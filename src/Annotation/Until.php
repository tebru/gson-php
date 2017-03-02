<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Annotation;

use LogicException;

/**
 * Class Until
 *
 * Used to exclude classes or properties that should not be serialized if the version number
 * is greater than or equal to the @Until value.  Will not be used if version number is not
 * defined on the builder.
 *
 * @author Nate Brunette <n@tebru.net>
 *
 * @Annotation
 * @Target({"CLASS", "PROPERTY", "METHOD"})
 */
class Until
{
    /**
     * @var string
     */
    private $value;

    /**
     * Constructor
     *
     * @param array $params
     * @throws \LogicException If the version is not specified
     */
    public function __construct(array $params)
    {
        if (!isset($params['value'])) {
            throw new LogicException('@Until annotation must specify a version as the first argument');
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
