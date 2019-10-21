<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock;

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
     * be serialized. Delegate serialization using the provided context.
     *
     * @param UserMock $object
     * @param TypeToken $type
     * @param JsonSerializationContext $context
     * @return mixed
     */
    public function serialize($object, TypeToken $type, JsonSerializationContext $context): array
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
