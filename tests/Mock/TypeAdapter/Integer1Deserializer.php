<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock\TypeAdapter;

use DateTime;
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
}
