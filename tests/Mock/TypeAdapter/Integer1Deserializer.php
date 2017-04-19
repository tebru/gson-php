<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock\TypeAdapter;

use DateTime;
use Tebru\Gson\Element\JsonElement;
use Tebru\Gson\JsonDeserializationContext;
use Tebru\Gson\JsonDeserializer;
use Tebru\Gson\Test\Mock\GsonMock;
use Tebru\Gson\Test\Mock\GsonObjectMock;
use Tebru\PhpType\TypeToken;

/**
 * Class Integer1Deserializer
 *
 * @author Nate Brunette <n@tebru.net>
 */
class Integer1Deserializer implements JsonDeserializer
{
    /**
     * Called during deserialization process, passing in the JsonElement for the type.  Use
     * the JsonDeserializationContext if you want to delegate deserialization of sub types.
     *
     * @param JsonElement $jsonElement
     * @param TypeToken $type
     * @param JsonDeserializationContext $context
     * @return mixed
     */
    public function deserialize(JsonElement $jsonElement, TypeToken $type, JsonDeserializationContext $context): GsonMock
    {
        $json = $jsonElement->asJsonObject();

        $mock = new GsonMock();
        $mock->setInteger($json->getAsInteger('integer') + 1);
        $mock->setFloat($json->getAsFloat('float'));
        $mock->setString($json->getAsString('string'));
        $mock->setBoolean($json->getAsBoolean('boolean'));
        $mock->setArray($json->getAsArray('array'));
        $mock->setDate($context->deserialize($json->get('date'), DateTime::class));
        $mock->public = $json->getAsString('public');
        $mock->setSince($json->getAsString('since'));
        $mock->setUntil($json->getAsString('until'));
        $mock->setMyAccessor($json->getAsString('accessor'));
        $mock->setSerializedname($json->getAsString('serialized_name'));

        $array = $json->getAsArray('type');
        $array = array_map(function (int $value) { return $value + 1; }, $array);
        $mock->setType($array);

        $mock->setJsonAdapter(new GsonObjectMock($json->getAsString('json_adapter')));
        $mock->setExpose($json->getAsBoolean('expose'));
        $mock->setExcludeFromStrategy($json->getAsBoolean('exclude_from_strategy'));
        $mock->setGsonObjectMock($context->deserialize($json->get('gson_object_mock'), GsonObjectMock::class));

        return $mock;
    }
}
