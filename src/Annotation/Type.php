<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Annotation;

use LogicException;
use Tebru\Gson\Internal\DefaultPhpType;
use Tebru\Gson\PhpType;

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
     * An object representation of the php type
     *
     * @var PhpType
     */
    private $value;

    /**
     * A map of additional data that might be associated with the type
     *
     * For example, a DateTime object might need formatting options
     *
     *     @Type(DateTime::class, options={"format": "Y-m-d"})
     *
     * @var array
     */
    private $options = [];

    /**
     * Constructor
     *
     * @param array $params
     * @throws \LogicException If value does not exist in params array
     * @throws \RuntimeException If the value is not valid
     * @throws \Tebru\Gson\Exception\MalformedTypeException If the type cannot be parsed
     */
    public function __construct(array $params)
    {
        if (!isset($params['value'])) {
            throw new LogicException('@Type annotation must specify a type as the first argument');
        }

        if (isset($params['options'])) {
            $this->options = (array) $params['options'];
        }

        $this->value = new DefaultPhpType($params['value'], $this->options);
    }

    /**
     * Returns the php type
     *
     * @return PhpType
     */
    public function getType(): PhpType
    {
        return $this->value;
    }
}
