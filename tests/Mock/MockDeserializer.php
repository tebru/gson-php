<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock;

use Tebru\Gson\JsonDeserializationContext;
use Tebru\Gson\JsonDeserializer;
use Tebru\PhpType\TypeToken;

/**
 * Class MockDeserializer
 *
 * @author Nate Brunette <n@tebru.net>
 */
class MockDeserializer implements JsonDeserializer
{
    /**
     * Called during deserialization process, passing in the normalized data. Use
     * the JsonDeserializationContext if you want to delegate deserialization of sub types.
     *
     * @param mixed $value
     * @param TypeToken $type
     * @param JsonDeserializationContext $context
     * @return UserMock
     */
    public function deserialize($value, TypeToken $type, JsonDeserializationContext $context): UserMock
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
}
