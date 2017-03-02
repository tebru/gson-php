<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Annotation;

use LogicException;

/**
 * Class SerializedName
 *
 * Used to define the name that should appear in json.  This annotation will override
 * any PropertyNamingStrategy.
 *
 * @author Nate Brunette <n@tebru.net>
 *
 * @Annotation
 * @Target({"PROPERTY", "METHOD"})
 */
class SerializedName
{
    /**
     * @var string
     */
    private $value;

    /**
     * Constructor
     *
     * @param array $params
     * @throws \LogicException If name isn't provided
     */
    public function __construct(array $params)
    {
        if (!isset($params['value'])) {
            throw new LogicException('@SerializedName annotation must specify a name as the first argument');
        }

        $this->value = $params['value'];
    }

    /**
     * Get the serialized name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->value;
    }
}
