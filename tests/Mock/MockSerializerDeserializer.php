<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock;

use Tebru\Gson\JsonDeserializationContext;
use Tebru\Gson\JsonDeserializer;
use Tebru\Gson\JsonSerializationContext;
use Tebru\Gson\JsonSerializer;
use Tebru\PhpType\TypeToken;

/**
 * Class MockSerializerDeserializer
 *
 * @author Nate Brunette <n@tebru.net>
 */
class MockSerializerDeserializer implements JsonSerializer, JsonDeserializer
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
        $user = new UserMock();
        $user->setId($value['id']);
        $user->setEmail($value['email']);
        $user->setName($value['name']);
        $user->setPhone($value['phone']);
        $user->setEnabled($value['enabled']);

        $address = new AddressMock();
        $address->setCity($value['city']);
        $address->setState($value['state']);
        $address->setStreet($value['street']);
        $address->setZip($value['zip']);

        $user->setAddress($address);

        return $user;
    }

    /**
     * Called during serialization process, passing in the object and type that should
     * be serialized. Delegate serialization using the provided context.
     *
     * @param UserMock $object
     * @param TypeToken $type
     * @param JsonSerializationContext $context
     * @return mixed
     */
    public function serialize($object, TypeToken $type, JsonSerializationContext $context)
    {
        $address = $object->getAddress();

        return [
            'id' => $object->getId(),
            'email' => $object->getEmail(),
            'name' => $object->getName(),
            'phone' => $object->getPhone(),
            'enabled' => $object->isEnabled(),
            'city' => $address->getCity(),
            'state' => $address->getState(),
            'street' => $address->getStreet(),
            'zip' => $address->getZip(),
        ];
    }
}
