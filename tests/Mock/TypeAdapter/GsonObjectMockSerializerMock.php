<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock\TypeAdapter;

use Tebru\Gson\Element\JsonElement;
use Tebru\Gson\Internal\PhpType;
use Tebru\Gson\JsonDeserializationContext;
use Tebru\Gson\JsonDeserializer;
use Tebru\Gson\Test\Mock\GsonObjectMock;

/**
 * Class GsonObjectMockSerializerMock
 *
 * @author Nate Brunette <n@tebru.net>
 */
class GsonObjectMockSerializerMock implements JsonDeserializer
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
    public function deserialize(JsonElement $jsonElement, PhpType $type, JsonDeserializationContext $context): GsonObjectMock
    {
        return new GsonObjectMock($jsonElement->asString());
    }
}
