<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter;


use InvalidArgumentException;
use PHPUnit_Framework_TestCase;
use Tebru\Gson\Exception\UnexpectedJsonTokenException;
use Tebru\Gson\Internal\JsonDecodeReader;
use Tebru\Gson\Internal\DefaultPhpType;


use Tebru\Gson\Internal\TypeAdapter\WildcardTypeAdapter;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\Test\MockProvider;

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
        $adapter = $this->typeAdapterProvider->getAdapter(new DefaultPhpType('?'));

        $result = $adapter->readFromJson('[]');

        self::assertSame([], $result);
    }

    public function testDeserializeObject()
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new DefaultPhpType('?'));

        $result = $adapter->readFromJson('{}');

        self::assertSame([], $result);
    }

    public function testDeserializeString()
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new DefaultPhpType('?'));

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
        $adapter = $this->typeAdapterProvider->getAdapter(new DefaultPhpType('?'));

        $result = $adapter->readFromJson('true');

        self::assertTrue($result);
    }

    public function testDeserializeBooleanFalse()
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new DefaultPhpType('?'));

        $result = $adapter->readFromJson('false');

        self::assertFalse($result);
    }

    public function testDeserializeNumberInt()
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new DefaultPhpType('?'));

        $result = $adapter->readFromJson('1');

        self::assertSame(1.0, $result);
    }

    public function testDeserializeNumberFloat()
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new DefaultPhpType('?'));

        $result = $adapter->readFromJson('1.1');

        self::assertSame(1.1, $result);
    }

    public function testDeserializeNumberNull()
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new DefaultPhpType('?'));

        $result = $adapter->readFromJson('null');

        self::assertNull($result);
    }

    public function testDeserializeException()
    {
        $this->expectException(UnexpectedJsonTokenException::class);
        $this->expectExceptionMessage('Could not parse token "end-object"');

        $reader = new JsonDecodeReader('{"key": "value"}');
        $reader->beginObject();
        $reader->nextName();
        $reader->nextString();
        $adapter = new WildcardTypeAdapter($this->typeAdapterProvider);
        $result = $adapter->read($reader);

        self::assertSame('key', $result);
    }

    public function testSerializeArray()
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new DefaultPhpType('?'));

        self::assertSame('[]', $adapter->writeToJson([], false));
    }

    public function testSerializeObject()
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new DefaultPhpType('?'));

        self::assertSame('{"foo":"bar"}', $adapter->writeToJson(['foo' => 'bar'], false));
    }

    public function testSerializeString()
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new DefaultPhpType('?'));

        self::assertSame('"foo"', $adapter->writeToJson('foo', false));
    }

    public function testSerializeBooleanTrue()
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new DefaultPhpType('?'));

        self::assertSame('true', $adapter->writeToJson(true, false));
    }

    public function testSerializeBooleanFalse()
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new DefaultPhpType('?'));

        self::assertSame('false', $adapter->writeToJson(false, false));
    }

    public function testSerializeInteger()
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new DefaultPhpType('?'));

        self::assertSame('1', $adapter->writeToJson(1, false));
    }

    public function testSerializeNull()
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new DefaultPhpType('?'));

        self::assertSame('null', $adapter->writeToJson(null, false));
    }

    public function testSerializeResource()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The type "resource" could not be handled by any of the registered type adapters');

        $adapter = $this->typeAdapterProvider->getAdapter(new DefaultPhpType('?'));

        $adapter->writeToJson(fopen(__FILE__, 'rb'), false);
    }
}
