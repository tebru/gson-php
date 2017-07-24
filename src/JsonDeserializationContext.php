<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson;

use Tebru\Gson\Element\JsonElement;

/**
 * Interface JsonDeserializationContext
 *
 * An instance of this interface will be passed to a custom deserializer.  Use this
 * instance to delegate deserialization.
 *
 * @author Nate Brunette <n@tebru.net>
 */
interface JsonDeserializationContext
{
    /**
     * Delegate deserialization of a JsonElement.  Should not be called on the original
     * element as doing so will result in an infinite loop.  Should return a deserialized
     * object.
     *
     * @param JsonElement $jsonElement
     * @param string $type
     * @return mixed
     */
    public function deserialize(JsonElement $jsonElement, string $type);
}
