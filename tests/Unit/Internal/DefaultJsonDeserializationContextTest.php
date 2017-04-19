<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
namespace Tebru\Gson\Test\Unit\Internal;

use PHPUnit_Framework_TestCase;
use Tebru\Gson\Element\JsonObject;
use Tebru\Gson\Test\Mock\AddressMock;
use Tebru\Gson\Test\MockProvider;
use Tebru\PhpType\TypeToken;

/**
 * Class DefaultJsonDeserializationContextTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\DefaultJsonDeserializationContext
 * @covers \Tebru\Gson\TypeAdapter
 */
class DefaultJsonDeserializationContextTest extends PHPUnit_Framework_TestCase
{
    public function testDeserialize()
    {
        $jsonObject = new JsonObject();
        $jsonObject->addString('street', '123 ABC St');
        $jsonObject->addString('city', 'Foo');
        $jsonObject->addString('state', 'MN');
        $jsonObject->addInteger('zip', 12345);

        $context = MockProvider::deserializationContext(MockProvider::excluder());

        /** @var AddressMock $address */
        $address = $context->deserialize($jsonObject, new TypeToken(AddressMock::class));

        self::assertSame('123 ABC St', $address->getStreet());
        self::assertSame('Foo', $address->getCity());
        self::assertSame('MN', $address->getState());
        self::assertSame(12345, $address->getZip());
    }
}
