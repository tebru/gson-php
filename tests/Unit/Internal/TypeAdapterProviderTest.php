<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
namespace Tebru\Gson\Test\Unit\Internal;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\VoidCache;
use InvalidArgumentException;
use PHPUnit_Framework_TestCase;
use ReflectionProperty;
use stdClass;
use Tebru\Gson\Annotation\JsonAdapter;
use Tebru\Gson\Internal\ConstructorConstructor;
use Tebru\Gson\Internal\DefaultPhpType;
use Tebru\Gson\Internal\TypeAdapter\CustomWrappedTypeAdapter;
use Tebru\Gson\Internal\TypeAdapter\Factory\StringTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\WrappedTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\StringTypeAdapter;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\Test\Mock\ChildClass;
use Tebru\Gson\Test\Mock\MockDeserializer;
use Tebru\Gson\Test\Mock\MockSerializer;
use Tebru\Gson\Test\Mock\MockSerializerDeserializer;
use Tebru\Gson\Test\Mock\TypeAdapterMock;
use Tebru\Gson\Test\Mock\TypeAdapterMockable;
use Tebru\Gson\Test\MockProvider;

/**
 * Class TypeAdapterProviderTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\TypeAdapterProvider
 */
class TypeAdapterProviderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ArrayCache
     */
    private $cache;

    /**
     * @var TypeAdapterMock
     */
    private $typeAdapterMock;

    /**
     * @var TypeAdapterProvider
     */
    private $typeAdapterProvider;
    
    public function setUp()
    {
        $this->cache = new ArrayCache();
        $this->typeAdapterMock = new TypeAdapterMock();
        $this->typeAdapterProvider = MockProvider::typeAdapterProvider(MockProvider::excluder(), [$this->typeAdapterMock], $this->cache);
    }

    public function testAddTypeAdapter()
    {
        $this->typeAdapterProvider->addTypeAdapter('string', new TypeAdapterMock());
        $adapter = $this->typeAdapterProvider->getAdapter(new DefaultPhpType('string'));

        self::assertInstanceOf(TypeAdapterMock::class, $adapter);
    }

    public function testGetTypeAdapter()
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new DefaultPhpType('string'));

        self::assertInstanceOf(TypeAdapterMock::class, $adapter);
    }

    public function testGetTypeAdapterInterface()
    {
        $typeAdapterProvider = MockProvider::typeAdapterProvider(null, [new WrappedTypeAdapterFactory(new TypeAdapterMock(), new DefaultPhpType(TypeAdapterMockable::class))]);
        $adapter = $typeAdapterProvider->getAdapter(new DefaultPhpType(TypeAdapterMock::class));

        self::assertInstanceOf(TypeAdapterMock::class, $adapter);
    }

    public function testGetTypeAdapterThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The type "stdClass" could not be handled by any of the registered type adapters');

        $typeAdapterProvider = new TypeAdapterProvider([], new VoidCache(), new ConstructorConstructor());
        $typeAdapterProvider->getAdapter(new DefaultPhpType(stdClass::class));
    }

    public function testGetTypeAdapterSkipClass()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The type "string" could not be handled by any of the registered type adapters');

        $mock = new TypeAdapterMock();
        $typeAdapterProvider = new TypeAdapterProvider([$mock], new VoidCache(), new ConstructorConstructor());
        $typeAdapterProvider->getAdapter(new DefaultPhpType('string'), $mock);
    }

    public function testGetTypeAdapterUsesCache()
    {
        $this->typeAdapterProvider->getAdapter(new DefaultPhpType('string'));

        $reflectionProperty = new ReflectionProperty(TypeAdapterProvider::class, 'typeAdapterFactories');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->typeAdapterProvider, []);

        self::assertInstanceOf(TypeAdapterMock::class, $this->typeAdapterProvider->getAdapter(new DefaultPhpType('string')));
    }

    public function testGetTypeAdapterSkipsCache()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The type "string" could not be handled by any of the registered type adapters');

        $this->typeAdapterProvider->getAdapter(new DefaultPhpType('string'));

        $reflectionProperty = new ReflectionProperty(TypeAdapterProvider::class, 'typeAdapterFactories');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->typeAdapterProvider, []);

        self::assertInstanceOf(TypeAdapterMock::class, $this->typeAdapterProvider->getAdapter(new DefaultPhpType('string'), $this->typeAdapterMock));
    }

    public function testGetTypeAdapterFromAnnotation()
    {
        $adapter = $this->typeAdapterProvider->getAdapterFromAnnotation(new DefaultPhpType('string'), new JsonAdapter(['value' => TypeAdapterMock::class]));

        self::assertInstanceOf(TypeAdapterMock::class, $adapter);
    }

    public function testGetTypeAdapterFactoryFromAnnotation()
    {
        $adapter = $this->typeAdapterProvider->getAdapterFromAnnotation(new DefaultPhpType('string'), new JsonAdapter(['value' => StringTypeAdapterFactory::class]));

        self::assertInstanceOf(StringTypeAdapter::class, $adapter);
    }

    public function testGetJsonSerializerFromAnnotation()
    {
        $adapter = $this->typeAdapterProvider->getAdapterFromAnnotation(new DefaultPhpType('string'), new JsonAdapter(['value' => MockSerializer::class]));

        self::assertInstanceOf(CustomWrappedTypeAdapter::class, $adapter);
        self::assertAttributeInstanceOf(MockSerializer::class, 'serializer', $adapter);
        self::assertAttributeSame(null, 'deserializer', $adapter);
    }

    public function testGetJsonDeserializerFromAnnotation()
    {
        $adapter = $this->typeAdapterProvider->getAdapterFromAnnotation(new DefaultPhpType('string'), new JsonAdapter(['value' => MockDeserializer::class]));

        self::assertInstanceOf(CustomWrappedTypeAdapter::class, $adapter);
        self::assertAttributeSame(null, 'serializer', $adapter);
        self::assertAttributeInstanceOf(MockDeserializer::class, 'deserializer', $adapter);
    }

    public function testGetTypeAdapterFromAnnotationException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The type adapter must be an instance of TypeAdapter, TypeAdapterFactory, JsonSerializer, or JsonDeserializer, but "Tebru\Gson\Test\Mock\ChildClass" was found');

        $this->typeAdapterProvider->getAdapterFromAnnotation(new DefaultPhpType('string'), new JsonAdapter(['value' => ChildClass::class]));
    }

    public function testGetJsonSerializerAndDeserializerFromAnnotation()
    {
        $adapter = $this->typeAdapterProvider->getAdapterFromAnnotation(new DefaultPhpType('string'), new JsonAdapter(['value' => MockSerializerDeserializer::class]));

        self::assertInstanceOf(CustomWrappedTypeAdapter::class, $adapter);
        self::assertAttributeInstanceOf(MockSerializerDeserializer::class, 'serializer', $adapter);
        self::assertAttributeInstanceOf(MockSerializerDeserializer::class, 'deserializer', $adapter);
    }
}
