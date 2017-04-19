<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock;

use Tebru\Gson\Element\JsonElement;
use Tebru\Gson\Element\JsonObject;
use Tebru\Gson\JsonSerializationContext;
use Tebru\Gson\JsonSerializer;
use Tebru\PhpType\TypeToken;

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
     * @param UserMock $object
     * @param TypeToken $type
     * @param JsonSerializationContext $context
     * @return JsonElement
     */
    public function serialize($object, TypeToken $type, JsonSerializationContext $context): JsonElement
    {
        $jsonUser = new JsonObject();
        $jsonUser->addInteger('id', $object->getId());
        $jsonUser->addString('email', $object->getEmail());
        $jsonUser->addString('name', $object->getName());
        $jsonUser->addString('phone', $object->getPhone());
        $jsonUser->addBoolean('enabled', $object->isEnabled());

        $address = $object->getAddress();

        $jsonUser->addString('city', $address->getCity());
        $jsonUser->addString('state', $address->getState());
        $jsonUser->addString('street', $address->getStreet());
        $jsonUser->addInteger('zip', $address->getZip());

        return $jsonUser;
    }
}
