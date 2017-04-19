<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock;

use Tebru\Gson\Element\JsonElement;
use Tebru\Gson\Element\JsonObject;
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
     * Called during deserialization process, passing in the JsonElement for the type.  Use
     * the JsonDeserializationContext if you want to delegate deserialization of sub types.
     *
     * @param JsonElement $jsonElement
     * @param TypeToken $type
     * @param JsonDeserializationContext $context
     * @return UserMock
     */
    public function deserialize(JsonElement $jsonElement, TypeToken $type, JsonDeserializationContext $context): UserMock
    {
        /** @var JsonObject $jsonUser */
        $jsonUser = $jsonElement;
        $user = new UserMock();
        $user->setId($jsonUser->get('id')->asInteger());
        $user->setEmail($jsonUser->get('email')->asString());
        $user->setName($jsonUser->get('name')->asString());
        $user->setPhone($jsonUser->get('phone')->asString());
        $user->setEnabled($jsonUser->get('enabled')->asBoolean());

        $address = new AddressMock();
        $address->setCity($jsonUser->get('city')->asString());
        $address->setState($jsonUser->get('state')->asString());
        $address->setStreet($jsonUser->get('street')->asString());
        $address->setZip($jsonUser->get('zip')->asInteger());

        $user->setAddress($address);

        return $user;
    }
}
