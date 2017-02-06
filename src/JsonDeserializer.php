<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson;

use Tebru\Gson\Element\JsonElement;
use Tebru\Gson\Internal\PhpType;

/**
 * Interface JsonDeserializer
 *
 * Defines a custom deserializer for a specific type.
 *
 * @author Nate Brunette <n@tebru.net>
 */
interface JsonDeserializer
{
    /**
     * Called during deserialization process, passing in the JsonElement for the type.  Use
     * the JsonDeserializationContext if you want to delegate deserialization of sub types.
     *
     * @param JsonElement $jsonElement
     * @param PhpType $type
     * @param JsonDeserializationContext $context
     * @return mixed
     */
    public function deserialize(JsonElement $jsonElement, PhpType $type, JsonDeserializationContext $context);
}
