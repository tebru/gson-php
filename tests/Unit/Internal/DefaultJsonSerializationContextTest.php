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

/**
 * Class DefaultJsonSerializationContextTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\DefaultJsonSerializationContext
 * @covers \Tebru\Gson\TypeAdapter
 */
class DefaultJsonSerializationContextTest extends PHPUnit_Framework_TestCase
{
    public function testSerialize()
    {
        $address = new AddressMock();
        $address->setStreet('123 ABC St');
        $address->setCity('Foo');
        $address->setState('MN');
        $address->setZip('12345');

        $context = MockProvider::serializationContext(MockProvider::excluder());

        /** @var JsonObject $addressElement */
        $addressElement = $context->serialize($address);

        self::assertSame('123 ABC St', $addressElement->getAsString('street'));
        self::assertSame('Foo', $addressElement->getAsString('city'));
        self::assertSame('MN', $addressElement->getAsString('state'));
        self::assertSame(12345, $addressElement->getAsInteger('zip'));
    }
}
