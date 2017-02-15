<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
namespace Tebru\Gson\Test\Unit\Internal;

use InvalidArgumentException;
use PHPUnit_Framework_TestCase;
use ReflectionProperty;
use Tebru\Gson\Annotation\JsonAdapter;
use Tebru\Gson\Internal\PhpType;
use Tebru\Gson\Internal\TypeAdapter\CustomWrappedTypeAdapter;
use Tebru\Gson\Internal\TypeAdapter\Factory\StringTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\StringTypeAdapter;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\Test\Mock\ChildClass;
use Tebru\Gson\Test\Mock\MockDeserializer;
use Tebru\Gson\Test\Mock\MockSerializer;
use Tebru\Gson\Test\Mock\TypeAdapterMock;

/**
 * Class TypeAdapterProviderTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\TypeAdapterProvider
 */
class TypeAdapterProviderTest extends PHPUnit_Framework_TestCase
{
    public function testAddTypeAdapter()
    {
        $provider = new TypeAdapterProvider([new TypeAdapterMock()]);
        $provider->addTypeAdapter('string', new TypeAdapterMock());
        $adapter = $provider->getAdapter(new PhpType('string'));

        self::assertInstanceOf(TypeAdapterMock::class, $adapter);
    }
    public function testGetTypeAdapter()
    {
        $provider = new TypeAdapterProvider([new TypeAdapterMock()]);
        $adapter = $provider->getAdapter(new PhpType('string'));

        self::assertInstanceOf(TypeAdapterMock::class, $adapter);
    }

    public function testGetTypeAdapterThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The type "foo" could not be handled by any of the registered type adapters');

        $provider = new TypeAdapterProvider([new TypeAdapterMock()]);
        $provider->getAdapter(new PhpType('foo'));
    }

    public function testGetTypeAdapterSkipClass()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The type "string" could not be handled by any of the registered type adapters');

        $mock = new TypeAdapterMock();
        $provider = new TypeAdapterProvider([$mock]);
        $provider->getAdapter(new PhpType('string'), $mock);
    }

    public function testGetTypeAdapterUsesCache()
    {
        $provider = new TypeAdapterProvider([new TypeAdapterMock()]);
        $provider->getAdapter(new PhpType('string'));

        $reflectionProperty = new ReflectionProperty(TypeAdapterProvider::class, 'typeAdapterFactories');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($provider, []);

        self::assertInstanceOf(TypeAdapterMock::class, $provider->getAdapter(new PhpType('string')));
    }

    public function testGetTypeAdapterSkipsCache()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The type "string" could not be handled by any of the registered type adapters');

        $mock = new TypeAdapterMock();
        $provider = new TypeAdapterProvider([$mock]);
        $provider->getAdapter(new PhpType('string'));

        $reflectionProperty = new ReflectionProperty(TypeAdapterProvider::class, 'typeAdapterFactories');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($provider, []);

        self::assertInstanceOf(TypeAdapterMock::class, $provider->getAdapter(new PhpType('string'), $mock));
    }

    public function testGetTypeAdapterFromAnnotation()
    {
        $provider = new TypeAdapterProvider([new TypeAdapterMock()]);
        $adapter = $provider->getAdapterFromAnnotation(new PhpType('string'), new JsonAdapter(['value' => TypeAdapterMock::class]));

        self::assertInstanceOf(TypeAdapterMock::class, $adapter);
    }

    public function testGetTypeAdapterFactoryFromAnnotation()
    {
        $provider = new TypeAdapterProvider([new TypeAdapterMock()]);
        $adapter = $provider->getAdapterFromAnnotation(new PhpType('string'), new JsonAdapter(['value' => StringTypeAdapterFactory::class]));

        self::assertInstanceOf(StringTypeAdapter::class, $adapter);
    }

    public function testGetJsonSerializerFromAnnotation()
    {
        $provider = new TypeAdapterProvider([new TypeAdapterMock()]);
        $adapter = $provider->getAdapterFromAnnotation(new PhpType('string'), new JsonAdapter(['value' => MockSerializer::class]));

        self::assertInstanceOf(CustomWrappedTypeAdapter::class, $adapter);
        self::assertAttributeInstanceOf(MockSerializer::class, 'serializer', $adapter);
        self::assertAttributeSame(null, 'deserializer', $adapter);
    }

    public function testGetJsonDeserializerFromAnnotation()
    {
        $provider = new TypeAdapterProvider([new TypeAdapterMock()]);
        $adapter = $provider->getAdapterFromAnnotation(new PhpType('string'), new JsonAdapter(['value' => MockDeserializer::class]));

        self::assertInstanceOf(CustomWrappedTypeAdapter::class, $adapter);
        self::assertAttributeSame(null, 'serializer', $adapter);
        self::assertAttributeInstanceOf(MockDeserializer::class, 'deserializer', $adapter);
    }

    public function testGetTypeAdapterFromAnnotationException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The type adapter must be an instance of TypeAdapter, TypeAdapterFactory, JsonSerializer, or JsonDeserializer, but "Tebru\Gson\Test\Mock\ChildClass" was found');

        $provider = new TypeAdapterProvider([new TypeAdapterMock()]);
        $provider->getAdapterFromAnnotation(new PhpType('string'), new JsonAdapter(['value' => ChildClass::class]));
    }
}
