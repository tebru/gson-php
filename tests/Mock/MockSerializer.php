<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock;

use Tebru\Gson\Element\JsonElement;
use Tebru\Gson\Element\JsonObject;
use Tebru\Gson\Internal\PhpType;
use Tebru\Gson\JsonSerializationContext;
use Tebru\Gson\JsonSerializer;

/**
 * Class MockSerializer
 *
 * @author Nate Brunette <n@tebru.net>
 */
class MockSerializer implements JsonSerializer
{
    /**
     * Called during serialization process, passing in the object and type that should
     * be serialized.  Delegate serialization using the provided context.  Method should
     * return a JsonElement.
     *
     * @param mixed $object
     * @param PhpType $type
     * @param JsonSerializationContext $context
     * @return JsonElement
     */
    public function serialize($object, PhpType $type, JsonSerializationContext $context): JsonElement
    {
        $jsonObject = new JsonObject();
        $jsonObject->addString('foo', 'bar');

        return $jsonObject;
    }
}
