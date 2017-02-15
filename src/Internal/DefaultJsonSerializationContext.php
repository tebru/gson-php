<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal;

use Tebru\Gson\Element\JsonElement;
use Tebru\Gson\JsonSerializationContext;

/**
 * Class DefaultJsonSerializationContext
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class DefaultJsonSerializationContext implements JsonSerializationContext
{
    /**
     * @var TypeAdapterProvider
     */
    private $typeAdapterProvider;

    /**
     * Constructor
     *
     * @param TypeAdapterProvider $typeAdapterProvider
     */
    public function __construct(TypeAdapterProvider $typeAdapterProvider)
    {
        $this->typeAdapterProvider = $typeAdapterProvider;
    }

    /**
     * Delegate serialization of an object.  Should never be called with the original object
     * as doing so will result in an infinite loop.  Will return a JsonElement.
     *
     * @param mixed $object
     * @param string $type
     * @return JsonElement
     * @throws \InvalidArgumentException if the type cannot be handled by a type adapter
     * @throws \Tebru\Gson\Exception\MalformedTypeException If the type cannot be parsed
     */
    public function serialize($object, string $type): JsonElement
    {
        $typeAdapter = $this->typeAdapterProvider->getAdapter(new PhpType($type));

        return $typeAdapter->writeToJsonElement($object);
    }
}
