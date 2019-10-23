<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock\TypeAdapter;

use Tebru\Gson\JsonSerializationContext;
use Tebru\Gson\JsonSerializer;
use Tebru\Gson\Test\Mock\GsonMock;
use Tebru\PhpType\TypeToken;

/**
 * Class Integer1Serializer
 *
 * @author Nate Brunette <n@tebru.net>
 */
class Integer1Serializer implements JsonSerializer
{
    /**
     * Called during serialization process, passing in the object and type that should
     * be serialized. Delegate serialization using the provided context.
     *
     * @param GsonMock $object
     * @param TypeToken $type
     * @param JsonSerializationContext $context
     * @return mixed
     */
    public function serialize($object, TypeToken $type, JsonSerializationContext $context)
    {
        $jsonArray = array_map(static function (int $item) { return $item + 1; }, $object->getType());

        return [
            'integer' => $object->getInteger() + 1,
            'float' => $object->getFloat(),
            'string' => $object->getString(),
            'boolean' => $object->getBoolean(),
            'array' => $context->serialize($object->getArray()),
            'date' => $context->serialize($object->getDate()),
            'public' => $object->public,
            'since' => $object->getSince(),
            'until' => $object->getUntil(),
            'accessor' => $object->getMyAccessor(),
            'serialized_name' => $object->getSerializedname(),
            'type' => $jsonArray,
            'json_adapter' => $object->getJsonAdapter()->getFoo(),
            'expose' => $object->getExpose(),
            'exclude_from_strategy' => $object->getExcludeFromStrategy(),
            'gson_object_mock' => $context->serialize($object->getGsonObjectMock()),
        ];
    }
}
