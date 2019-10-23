<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
namespace Tebru\Gson\Test\Unit\Internal;

use PHPUnit\Framework\TestCase;
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
class DefaultJsonDeserializationContextTest extends TestCase
{
    public function testDeserialize(): void
    {
        $data = [
            'street' => '123 ABC St',
            'city' => 'Foo',
            'state' => 'MN',
            'zip' => 12345,
        ];

        $context = MockProvider::deserializationContext(MockProvider::excluder());

        /** @var AddressMock $address */
        $address = $context->deserialize($data, new TypeToken(AddressMock::class));

        self::assertSame('123 ABC St', $address->getStreet());
        self::assertSame('Foo', $address->getCity());
        self::assertSame('MN', $address->getState());
        self::assertSame(12345, $address->getZip());
    }
}
