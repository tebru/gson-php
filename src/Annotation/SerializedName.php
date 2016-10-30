<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Annotation;

/**
 * Class SerializedName
 *
 * Used to define the name that should appear in json.  This annotation will override
 * any PropertyNamingStrategy.
 *
 * @author Nate Brunette <n@tebru.net>
 *
 * @Annotation
 * @Target({"PROPERTY"})
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
     */
    public function __construct(array $params)
    {
        if (array_key_exists('value', $params)) {
            $this->value = $params['value'];
        }
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
