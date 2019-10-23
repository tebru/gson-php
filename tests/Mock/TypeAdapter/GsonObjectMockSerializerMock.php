<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock\TypeAdapter;

use Tebru\Gson\JsonDeserializationContext;
use Tebru\Gson\JsonDeserializer;
use Tebru\Gson\JsonSerializationContext;
use Tebru\Gson\JsonSerializer;
use Tebru\Gson\Test\Mock\GsonObjectMock;
use Tebru\PhpType\TypeToken;

/**
 * Class GsonObjectMockSerializerMock
 *
 * @author Nate Brunette <n@tebru.net>
 */
class GsonObjectMockSerializerMock implements JsonSerializer, JsonDeserializer
{
    /**
     * Called during deserialization process, passing in the normalized data. Use
     * the JsonDeserializationContext if you want to delegate deserialization of sub types.
     *
     * @param mixed $value
     * @param TypeToken $type
     * @param JsonDeserializationContext $context
     * @return mixed
     */
    public function deserialize($value, TypeToken $type, JsonDeserializationContext $context)
    {
        if (is_object($value)) {
            return new GsonObjectMock($value['foo']);
        }

        return new GsonObjectMock($value);
    }

    /**
     * Called during serialization process, passing in the object and type that should
     * be serialized. Delegate serialization using the provided context.
     *
     * @param GsonObjectMock $object
     * @param TypeToken $type
     * @param JsonSerializationContext $context
     * @return mixed
     */
    public function serialize($object, TypeToken $type, JsonSerializationContext $context)
    {
        return $object->getFoo();
    }
}
