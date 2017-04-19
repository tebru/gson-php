<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock\TypeAdapter;

use Tebru\Gson\Element\JsonArray;
use Tebru\Gson\Element\JsonElement;
use Tebru\Gson\Element\JsonObject;
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
     * be serialized.  Delegate serialization using the provided context.  Method should
     * return a JsonElement.
     *
     * @param GsonMock $object
     * @param TypeToken $type
     * @param JsonSerializationContext $context
     * @return JsonElement
     */
    public function serialize($object, TypeToken $type, JsonSerializationContext $context): JsonElement
    {
        $jsonObject = new JsonObject();
        $jsonObject->addInteger('integer', $object->getInteger() + 1);
        $jsonObject->addFloat('float', $object->getFloat());
        $jsonObject->addString('string', $object->getString());
        $jsonObject->addBoolean('boolean', $object->getBoolean());
        $jsonObject->add('array', $context->serialize($object->getArray()));
        $jsonObject->add('date', $context->serialize($object->getDate()));
        $jsonObject->addString('public', $object->public);
        $jsonObject->addString('since', $object->getSince());
        $jsonObject->addString('until', $object->getUntil());
        $jsonObject->addString('accessor', $object->getMyAccessor());
        $jsonObject->addString('serialized_name', $object->getSerializedname());

        $jsonArray = new JsonArray();
        foreach ($object->getType() as $item) {
            $jsonArray->addInteger($item + 1);
        }
        $jsonObject->add('type', $jsonArray);

        $jsonObject->addString('json_adapter', $object->getJsonAdapter()->getFoo());
        $jsonObject->addBoolean('expose', $object->getExpose());
        $jsonObject->addBoolean('exclude_from_strategy', $object->getExcludeFromStrategy());
        $jsonObject->add('gson_object_mock', $context->serialize($object->getGsonObjectMock()));

        return $jsonObject;
    }
}
