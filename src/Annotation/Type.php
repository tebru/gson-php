<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Annotation;

use OutOfBoundsException;
use Tebru\PhpType\TypeToken;

/**
 * Class Type
 *
 * Used to define an explicit type for a property.  Generally a type will tried to
 * be inferred using the value, type, type hints, return types, or default values; otherwise,
 * the type will be assumed to be primitive.  This annotation lets you explicitly define
 * a type or create a custom type that can be used by a TypeAdapter.
 *
 * @author Nate Brunette <n@tebru.net>
 *
 * @Annotation
 * @Target({"PROPERTY", "METHOD"})
 */
class Type
{
    /**
     * The php type as a string
     *
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
            throw new OutOfBoundsException('@Type annotation must specify a type as the first argument');
        }

        $this->value = $params['value'];
    }

    /**
     * Returns the php type
     *
     * @return TypeToken
     * @throws \Tebru\PhpType\Exception\MalformedTypeException If the type cannot be parsed
     */
    public function getType(): TypeToken
    {
        return new TypeToken($this->value);
    }
}
