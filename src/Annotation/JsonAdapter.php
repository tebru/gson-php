<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Annotation;

use OutOfBoundsException;

/**
 * Class JsonAdapter
 *
 * Use this annotation to define a custom TypeAdapter for a class or property.  This annotation
 * cannot be used if the type of a property is ambiguous.  For example, if defined on a scalar
 * property that doesn't define an @Type annotation or provide an accessor with a type hint.
 *
 * The type adapter class should not be defined with any constructor arguments
 *
 * This annotation can point to a TypeAdapter, TypeAdapterFactory, JsonSerializer, or JsonDeserializer.
 *
 * @author Nate Brunette <n@tebru.net>
 *
 * @Annotation
 * @Target({"CLASS", "PROPERTY", "METHOD"})
 */
class JsonAdapter
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
            throw new OutOfBoundsException('@JsonAdapter annotation must specify a class as the first argument');
        }

        $this->value = $params['value'];
    }

    /**
     * Returns the class name of the custom adapter as a string
     *
     * @return string
     */
    public function getClass(): string
    {
        return $this->value;
    }
}
