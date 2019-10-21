<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock\TypeAdapter;

use DateTime;
use Tebru\Gson\JsonDeserializationContext;
use Tebru\Gson\JsonDeserializer;
use Tebru\Gson\JsonSerializationContext;
use Tebru\Gson\JsonSerializer;
use Tebru\Gson\Test\Mock\GsonMock;
use Tebru\Gson\Test\Mock\GsonObjectMock;
use Tebru\PhpType\TypeToken;

/**
 * Class Integer1SerializerDeserializer
 *
 * @author Nate Brunette <n@tebru.net>
 */
class Integer1SerializerDeserializer implements JsonDeserializer, JsonSerializer
{
    /**
     * Called during deserialization process, passing in the normalized data. Use
     * the JsonDeserializationContext if you want to delegate deserialization of sub types.
     *
     * @param array $value
     * @param TypeToken $type
     * @param JsonDeserializationContext $context
     * @return mixed
     */
    public function deserialize($value, TypeToken $type, JsonDeserializationContext $context)
    {
        $mock = new GsonMock();
        $mock->setInteger($value['integer'] + 1);
        $mock->setFloat($value['float']);
        $mock->setString($value['string']);
        $mock->setBoolean($value['boolean']);
        $mock->setArray($value['array']);
        $mock->setDate($context->deserialize($value['date'], DateTime::class));
        $mock->public = $value['public'];
        $mock->setSince($value['since']);
        $mock->setUntil($value['until']);
        $mock->setMyAccessor($value['accessor']);
        $mock->setSerializedname($value['serialized_name']);

        $array = $value['type'];
        $array = array_map(static function (int $value) { return $value + 1; }, $array);
        $mock->setType($array);

        $mock->setJsonAdapter(new GsonObjectMock($value['json_adapter']));
        $mock->setExpose($value['expose']);
        $mock->setExcludeFromStrategy($value['exclude_from_strategy']);
        $mock->setGsonObjectMock($context->deserialize($value['gson_object_mock'], GsonObjectMock::class));

        return $mock;
    }

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
        $array = array_map(static function (int $item) { return $item + 1; }, $object->getType());
        $jsonAdapter = ['foo' => $object->getJsonAdapter()->getFoo()];

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
            'type' => $array,
            'json_adapter' => $jsonAdapter,
            'expose' => $object->getExpose(),
            'exclude_from_strategy' => $object->getExcludeFromStrategy(),
            'gson_object_mock' => $context->serialize($object->getGsonObjectMock()),
        ];
    }
}
