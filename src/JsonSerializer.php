<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson;

use Tebru\Gson\Element\JsonElement;
use Tebru\Gson\Internal\PhpType;

/**
 * Interface JsonSerializer
 *
 * Defines a custom serializer for a specific type.
 *
 * @author Nate Brunette <n@tebru.net>
 */
interface JsonSerializer
{
    /**
     * Called during serialization process, passing in the object and type that should
     * be serialized.  Delegate serialization using the provided context.  Method should
     * return a JsonElement.
     *
     * @param mixed $object
     * @param PhpType $type
     * @param JsonSerializationContext $context
     * @return JsonElement
     */
    public function serialize($object, PhpType $type, JsonSerializationContext $context): JsonElement;
}
