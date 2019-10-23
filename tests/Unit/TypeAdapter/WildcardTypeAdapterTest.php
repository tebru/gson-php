<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
namespace Tebru\Gson\Test\Unit\TypeAdapter;

use InvalidArgumentException;
use Tebru\Gson\Context\ReaderContext;
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
class WildcardTypeAdapterTest extends TypeAdapterTestCase
{
    /**
     * @var TypeAdapterProvider
     */
    private $typeAdapterProvider;
    
    public function setUp()
    {
        parent::setUp();

        $this->typeAdapterProvider = MockProvider::typeAdapterProvider();
    }
    
    public function testDeserializeArray(): void
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));

        $result = $adapter->read(json_decode('[]', true), $this->readerContext);

        self::assertSame([], $result);
    }

    public function testDeserializeObject(): void
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));

        $result = $adapter->read(json_decode('{}', true), $this->readerContext);

        self::assertSame([], $result);
    }

    public function testDeserializeString(): void
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));

        $result = $adapter->read(json_decode('"foo"', true), $this->readerContext);

        self::assertSame('foo', $result);
    }

    public function testDeserializeStringWithoutScalarTypeAdapters(): void
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));
        $this->readerContext->setEnableScalarAdapters(false);

        $result = $adapter->read(json_decode('"foo"', true), $this->readerContext);

        self::assertSame('foo', $result);
    }

    public function testDeserializeName(): void
    {
        $adapter = new WildcardTypeAdapter($this->typeAdapterProvider);
        $result = $adapter->read('key', new ReaderContext());

        self::assertSame('key', $result);
    }

    public function testDeserializeBoolean(): void
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));

        $result = $adapter->read(json_decode('true', true), $this->readerContext);

        self::assertTrue($result);
    }

    public function testDeserializeBooleanFalse(): void
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));

        $result = $adapter->read(json_decode('false', true), $this->readerContext);

        self::assertFalse($result);
    }

    public function testDeserializeNumberInt(): void
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));

        $result = $adapter->read(json_decode('1', true), $this->readerContext);

        self::assertSame(1, $result);
    }

    public function testDeserializeNumberFloat(): void
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));

        $result = $adapter->read(json_decode('1.1', true), $this->readerContext);

        self::assertSame(1.1, $result);
    }

    public function testDeserializeNumberNull(): void
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));

        $result = $adapter->read(json_decode('null', true), $this->readerContext);

        self::assertNull($result);
    }

    public function testSerializeArray(): void
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));

        self::assertSame([], $adapter->write([], $this->writerContext));
    }

    public function testSerializeObject(): void
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));

        self::assertSame(['foo' => 'bar'], $adapter->write(['foo' => 'bar'], $this->writerContext));
    }

    public function testSerializeString(): void
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));

        self::assertSame('foo', $adapter->write('foo', $this->writerContext));
    }

    public function testSerializeStringWithoutScalarTypeAdapters(): void
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));
        $this->writerContext->setEnableScalarAdapters(false);

        self::assertSame('foo', $adapter->write('foo', $this->writerContext));
    }

    public function testSerializeBooleanTrue(): void
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));

        self::assertTrue($adapter->write(true, $this->writerContext));
    }

    public function testSerializeBooleanFalse(): void
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));

        self::assertFalse($adapter->write(false, $this->writerContext));
    }

    public function testSerializeInteger(): void
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));

        self::assertSame(1, $adapter->write(1, $this->writerContext));
    }

    public function testSerializeNull(): void
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));

        self::assertNull($adapter->write(null, $this->writerContext));
    }

    public function testSerializeResource(): void
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken('?'));

        try {
            $adapter->write(fopen(__FILE__, 'rb'), $this->writerContext);
        } catch (InvalidArgumentException $exception) {
            self::assertSame('The type "resource" could not be handled by any of the registered type adapters', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }
}
