<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson;

use Tebru\Gson\Element\JsonElement;
use Tebru\Gson\Internal\PhpType;

/**
 * Interface JsonSerializationContext
 *
 * Serialization context that is passed to a custom serializer.  Use this
 * context to delegate serialization.
 *
 * @author Nate Brunette <n@tebru.net>
 */
interface JsonSerializationContext
{
    /**
     * Delegate serialization of an object.  Should never be called with the original object
     * as doing so will result in an infinite loop.  Will return a JsonElement.
     *
     * @param mixed $object
     * @param PhpType $type
     * @return JsonElement
     */
    public function serialize($object, PhpType $type): JsonElement;
}
