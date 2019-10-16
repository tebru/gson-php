<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
namespace Tebru\Gson\Test\Unit\TypeAdapter;


use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Tebru\Gson\Exception\JsonSyntaxException;
use Tebru\Gson\Internal\DefaultReaderContext;
use Tebru\Gson\Internal\JsonDecodeReader;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\Test\MockProvider;
use Tebru\Gson\TypeAdapter\WildcardTypeAdapter;
use Tebru\PhpType\TypeToken;

/**
 * Class WildcardTypeAdapterTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\TypeAdapter\WildcardTypeAdapter
 * @covers \Tebru\Gson\TypeAdapter
 */
class WildcardTypeAdapterTest extends TestCase
{
    /**
     * @var TypeAdapterProvider
     */
    private $typeAdapterProvider;
    
    public function setUp()
    {
        $this->typeAdapterProvider = MockProvider::typeAdapterProvider();
    }
    
    public function testDeserializeArray(): void
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));

        $result = $adapter->readFromJson('[]');

        self::assertSame([], $result);
    }

    public function testDeserializeObject(): void
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));

        $result = $adapter->readFromJson('{}');

        self::assertSame([], $result);
    }

    public function testDeserializeString(): void
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));

        $result = $adapter->readFromJson('"foo"');

        self::assertSame('foo', $result);
    }

    public function testDeserializeName(): void
    {
        $reader = new JsonDecodeReader('{"key": "value"}', new DefaultReaderContext());
        $reader->beginObject();
        $adapter = new WildcardTypeAdapter($this->typeAdapterProvider);
        $result = $adapter->read($reader);

        self::assertSame('key', $result);
    }

    public function testDeserializeBoolean(): void
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));

        $result = $adapter->readFromJson('true');

        self::assertTrue($result);
    }

    public function testDeserializeBooleanFalse(): void
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));

        $result = $adapter->readFromJson('false');

        self::assertFalse($result);
    }

    public function testDeserializeNumberInt(): void
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));

        $result = $adapter->readFromJson('1');

        self::assertSame(1.0, $result);
    }

    public function testDeserializeNumberFloat(): void
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));

        $result = $adapter->readFromJson('1.1');

        self::assertSame(1.1, $result);
    }

    public function testDeserializeNumberNull(): void
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));

        $result = $adapter->readFromJson('null');

        self::assertNull($result);
    }

    public function testDeserializeException(): void
    {
        $reader = new JsonDecodeReader('{"key": "value"}', new DefaultReaderContext());
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

    public function testSerializeArray(): void
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));

        self::assertSame('[]', $adapter->writeToJson([], false));
    }

    public function testSerializeObject(): void
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));

        self::assertSame('{"foo":"bar"}', $adapter->writeToJson(['foo' => 'bar'], false));
    }

    public function testSerializeString(): void
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));

        self::assertSame('"foo"', $adapter->writeToJson('foo', false));
    }

    public function testSerializeBooleanTrue(): void
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));

        self::assertSame('true', $adapter->writeToJson(true, false));
    }

    public function testSerializeBooleanFalse(): void
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));

        self::assertSame('false', $adapter->writeToJson(false, false));
    }

    public function testSerializeInteger(): void
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));

        self::assertSame('1', $adapter->writeToJson(1, false));
    }

    public function testSerializeNull(): void
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));

        self::assertSame('null', $adapter->writeToJson(null, false));
    }

    public function testSerializeResource(): void
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
