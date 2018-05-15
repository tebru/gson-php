<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
namespace Tebru\Gson\Test\Unit\Internal;

use InvalidArgumentException;
use PHPUnit_Framework_TestCase;
use stdClass;
use Tebru\Gson\Annotation\JsonAdapter;
use Tebru\Gson\Internal\ConstructorConstructor;
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
use Tebru\PhpType\TypeToken;

/**
 * Class TypeAdapterProviderTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\TypeAdapterProvider
 */
class TypeAdapterProviderTest extends PHPUnit_Framework_TestCase
{
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
        $this->typeAdapterMock = new TypeAdapterMock();
        $this->typeAdapterProvider = MockProvider::typeAdapterProvider(MockProvider::excluder(), [$this->typeAdapterMock]);
    }

    public function testGetTypeAdapter()
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('string'));

        self::assertInstanceOf(TypeAdapterMock::class, $adapter);
    }

    public function testGetTypeAdapterInterface()
    {
        $typeAdapterProvider = MockProvider::typeAdapterProvider(null, [new WrappedTypeAdapterFactory(new TypeAdapterMock(), new TypeToken(TypeAdapterMockable::class), false)]);
        $adapter = $typeAdapterProvider->getAdapter(new TypeToken(TypeAdapterMock::class));

        self::assertInstanceOf(TypeAdapterMock::class, $adapter);
    }

    public function testGetTypeAdapterStrict()
    {
        $typeAdapterProvider = MockProvider::typeAdapterProvider(null, [new WrappedTypeAdapterFactory(new TypeAdapterMock(), new TypeToken(TypeAdapterMockable::class), true)]);
        $adapter = $typeAdapterProvider->getAdapter(new TypeToken(TypeAdapterMock::class));

        self::assertNotInstanceOf(TypeAdapterMock::class, $adapter);
    }

    public function testGetTypeAdapterThrowsException()
    {
        $typeAdapterProvider = new TypeAdapterProvider([], new ConstructorConstructor());
        try {
            $typeAdapterProvider->getAdapter(new TypeToken(stdClass::class));
        } catch (InvalidArgumentException $exception) {
            self::assertSame('The type "stdClass" could not be handled by any of the registered type adapters', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testGetTypeAdapterSkipClass()
    {
        $mock = new TypeAdapterMock();
        $typeAdapterProvider = new TypeAdapterProvider([$mock], new ConstructorConstructor());
        try {
            $typeAdapterProvider->getAdapter(new TypeToken('string'), $mock);
        } catch (InvalidArgumentException $exception) {
            self::assertSame('The type "string" could not be handled by any of the registered type adapters', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testGetTypeAdapterUsesCache()
    {
        $stringTypeAdapter = $this->typeAdapterProvider->getAdapter(new TypeToken('string'));

        self::assertSame($stringTypeAdapter, $this->typeAdapterProvider->getAdapter(new TypeToken('string')));
    }

    public function testGetTypeAdapterFromAnnotation()
    {
        $adapter = $this->typeAdapterProvider->getAdapterFromAnnotation(new TypeToken('string'), new JsonAdapter(['value' => TypeAdapterMock::class]));

        self::assertInstanceOf(TypeAdapterMock::class, $adapter);
    }

    public function testGetTypeAdapterFactoryFromAnnotation()
    {
        $adapter = $this->typeAdapterProvider->getAdapterFromAnnotation(new TypeToken('string'), new JsonAdapter(['value' => StringTypeAdapterFactory::class]));

        self::assertInstanceOf(StringTypeAdapter::class, $adapter);
    }

    public function testGetJsonSerializerFromAnnotation()
    {
        $adapter = $this->typeAdapterProvider->getAdapterFromAnnotation(new TypeToken('string'), new JsonAdapter(['value' => MockSerializer::class]));

        self::assertInstanceOf(CustomWrappedTypeAdapter::class, $adapter);
        self::assertAttributeInstanceOf(MockSerializer::class, 'serializer', $adapter);
        self::assertAttributeSame(null, 'deserializer', $adapter);
    }

    public function testGetJsonDeserializerFromAnnotation()
    {
        $adapter = $this->typeAdapterProvider->getAdapterFromAnnotation(new TypeToken('string'), new JsonAdapter(['value' => MockDeserializer::class]));

        self::assertInstanceOf(CustomWrappedTypeAdapter::class, $adapter);
        self::assertAttributeSame(null, 'serializer', $adapter);
        self::assertAttributeInstanceOf(MockDeserializer::class, 'deserializer', $adapter);
    }

    public function testGetTypeAdapterFromAnnotationException()
    {
        try {
            $this->typeAdapterProvider->getAdapterFromAnnotation(new TypeToken('string'), new JsonAdapter(['value' => ChildClass::class]));
        } catch (InvalidArgumentException $exception) {
            self::assertSame('The type adapter must be an instance of TypeAdapter, TypeAdapterFactory, JsonSerializer, or JsonDeserializer, but "Tebru\Gson\Test\Mock\ChildClass" was found', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testGetJsonSerializerAndDeserializerFromAnnotation()
    {
        $adapter = $this->typeAdapterProvider->getAdapterFromAnnotation(new TypeToken('string'), new JsonAdapter(['value' => MockSerializerDeserializer::class]));

        self::assertInstanceOf(CustomWrappedTypeAdapter::class, $adapter);
        self::assertAttributeInstanceOf(MockSerializerDeserializer::class, 'serializer', $adapter);
        self::assertAttributeInstanceOf(MockSerializerDeserializer::class, 'deserializer', $adapter);
    }
}
