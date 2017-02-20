<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
namespace Tebru\Gson\Test\Unit\Internal;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\VoidCache;
use PHPUnit_Framework_TestCase;
use Tebru\Gson\Element\JsonObject;
use Tebru\Gson\Internal\AccessorMethodProvider;
use Tebru\Gson\Internal\AccessorStrategyFactory;
use Tebru\Gson\Internal\ConstructorConstructor;
use Tebru\Gson\Internal\Data\AnnotationCollectionFactory;
use Tebru\Gson\Internal\Data\PropertyCollectionFactory;
use Tebru\Gson\Internal\Data\ReflectionPropertySetFactory;
use Tebru\Gson\Internal\DefaultJsonSerializationContext;
use Tebru\Gson\Internal\Excluder;
use Tebru\Gson\Internal\MetadataFactory;
use Tebru\Gson\Internal\Naming\PropertyNamer;
use Tebru\Gson\Internal\Naming\SnakePropertyNamingStrategy;
use Tebru\Gson\Internal\Naming\UpperCaseMethodNamingStrategy;
use Tebru\Gson\Internal\PhpTypeFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\IntegerTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\ReflectionTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\StringTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapterProvider;
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
