<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter;


use InvalidArgumentException;
use PHPUnit_Framework_TestCase;
use Tebru\Gson\Exception\JsonSyntaxException;
use Tebru\Gson\Internal\JsonDecodeReader;
use Tebru\Gson\Internal\TypeAdapter\WildcardTypeAdapter;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\Test\MockProvider;
use Tebru\PhpType\TypeToken;

/**
 * Class WildcardTypeAdapterTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\TypeAdapter\WildcardTypeAdapter
 * @covers \Tebru\Gson\TypeAdapter
 */
class WildcardTypeAdapterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var TypeAdapterProvider
     */
    private $typeAdapterProvider;
    
    public function setUp()
    {
        $this->typeAdapterProvider = MockProvider::typeAdapterProvider();
    }
    
    public function testDeserializeArray()
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));

        $result = $adapter->readFromJson('[]');

        self::assertSame([], $result);
    }

    public function testDeserializeObject()
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));

        $result = $adapter->readFromJson('{}');

        self::assertSame([], $result);
    }

    public function testDeserializeString()
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));

        $result = $adapter->readFromJson('"foo"');

        self::assertSame('foo', $result);
    }

    public function testDeserializeName()
    {
        $reader = new JsonDecodeReader('{"key": "value"}');
        $reader->beginObject();
        $adapter = new WildcardTypeAdapter($this->typeAdapterProvider);
        $result = $adapter->read($reader);

        self::assertSame('key', $result);
    }

    public function testDeserializeBoolean()
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));

        $result = $adapter->readFromJson('true');

        self::assertTrue($result);
    }

    public function testDeserializeBooleanFalse()
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));

        $result = $adapter->readFromJson('false');

        self::assertFalse($result);
    }

    public function testDeserializeNumberInt()
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));

        $result = $adapter->readFromJson('1');

        self::assertSame(1.0, $result);
    }

    public function testDeserializeNumberFloat()
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));

        $result = $adapter->readFromJson('1.1');

        self::assertSame(1.1, $result);
    }

    public function testDeserializeNumberNull()
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));

        $result = $adapter->readFromJson('null');

        self::assertNull($result);
    }

    public function testDeserializeException()
    {
        $reader = new JsonDecodeReader('{"key": "value"}');
        $reader->beginObject();
        $reader->nextName();
        $reader->nextString();
        $adapter = new WildcardTypeAdapter($this->typeAdapterProvider);

        try {
            $adapter->read($reader);
        } catch (JsonSyntaxException $exception) {
            self::assertSame('Could not parse token "end-object" at "$.key"', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testSerializeArray()
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));

        self::assertSame('[]', $adapter->writeToJson([], false));
    }

    public function testSerializeObject()
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));

        self::assertSame('{"foo":"bar"}', $adapter->writeToJson(['foo' => 'bar'], false));
    }

    public function testSerializeString()
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));

        self::assertSame('"foo"', $adapter->writeToJson('foo', false));
    }

    public function testSerializeBooleanTrue()
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));

        self::assertSame('true', $adapter->writeToJson(true, false));
    }

    public function testSerializeBooleanFalse()
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));

        self::assertSame('false', $adapter->writeToJson(false, false));
    }

    public function testSerializeInteger()
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));

        self::assertSame('1', $adapter->writeToJson(1, false));
    }

    public function testSerializeNull()
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));

        self::assertSame('null', $adapter->writeToJson(null, false));
    }

    public function testSerializeResource()
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));

        try {
            $adapter->writeToJson(fopen(__FILE__, 'rb'), false);
        } catch (InvalidArgumentException $exception) {
            self::assertSame('The type "resource" could not be handled by any of the registered type adapters', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }
}
