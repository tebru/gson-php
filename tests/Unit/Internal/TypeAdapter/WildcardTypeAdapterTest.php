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
use Tebru\Gson\Internal\PhpType;
use Tebru\Gson\Internal\TypeAdapter\Factory\ArrayTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\BooleanTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\FloatTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\IntegerTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\NullTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\StringTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\WildcardTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\WildcardTypeAdapter;
use Tebru\Gson\Internal\TypeAdapterProvider;

/**
 * Class WildcardTypeAdapterTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\TypeAdapter\WildcardTypeAdapter
 * @covers \Tebru\Gson\TypeAdapter
 */
class WildcardTypeAdapterTest extends PHPUnit_Framework_TestCase
{
    public function testDeserializeArray()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new ArrayTypeAdapterFactory(),
            new WildcardTypeAdapterFactory(),
        ]);

        $adapter = $typeAdapterProvider->getAdapter(new PhpType('?'));

        $result = $adapter->readFromJson('[]');

        self::assertSame([], $result);
    }

    public function testDeserializeObject()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new ArrayTypeAdapterFactory(),
            new WildcardTypeAdapterFactory(),
        ]);

        $adapter = $typeAdapterProvider->getAdapter(new PhpType('?'));

        $result = $adapter->readFromJson('{}');

        self::assertSame([], $result);
    }

    public function testDeserializeString()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new StringTypeAdapterFactory(),
            new WildcardTypeAdapterFactory(),
        ]);

        $adapter = $typeAdapterProvider->getAdapter(new PhpType('?'));

        $result = $adapter->readFromJson('"foo"');

        self::assertSame('foo', $result);
    }

    public function testDeserializeName()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new StringTypeAdapterFactory(),
            new WildcardTypeAdapterFactory(),
        ]);

        $reader = new JsonDecodeReader('{"key": "value"}');
        $reader->beginObject();
        $adapter = new WildcardTypeAdapter($typeAdapterProvider);
        $result = $adapter->read($reader);

        self::assertSame('key', $result);
    }

    public function testDeserializeBoolean()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new BooleanTypeAdapterFactory(),
            new WildcardTypeAdapterFactory(),
        ]);

        $adapter = $typeAdapterProvider->getAdapter(new PhpType('?'));

        $result = $adapter->readFromJson('true');

        self::assertTrue($result);
    }

    public function testDeserializeBooleanFalse()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new BooleanTypeAdapterFactory(),
            new WildcardTypeAdapterFactory(),
        ]);

        $adapter = $typeAdapterProvider->getAdapter(new PhpType('?'));

        $result = $adapter->readFromJson('false');

        self::assertFalse($result);
    }

    public function testDeserializeNumberInt()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new FloatTypeAdapterFactory(),
            new WildcardTypeAdapterFactory(),
        ]);

        $adapter = $typeAdapterProvider->getAdapter(new PhpType('?'));

        $result = $adapter->readFromJson('1');

        self::assertSame(1.0, $result);
    }

    public function testDeserializeNumberFloat()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new FloatTypeAdapterFactory(),
            new WildcardTypeAdapterFactory(),
        ]);

        $adapter = $typeAdapterProvider->getAdapter(new PhpType('?'));

        $result = $adapter->readFromJson('1.1');

        self::assertSame(1.1, $result);
    }

    public function testDeserializeNumberNull()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new NullTypeAdapterFactory(),
            new WildcardTypeAdapterFactory(),
        ]);

        $adapter = $typeAdapterProvider->getAdapter(new PhpType('?'));

        $result = $adapter->readFromJson('null');

        self::assertNull($result);
    }

    public function testDeserializeException()
    {
        $this->expectException(UnexpectedJsonTokenException::class);
        $this->expectExceptionMessage('Could not parse token "end-object"');

        $typeAdapterProvider = new TypeAdapterProvider([
            new StringTypeAdapterFactory(),
            new WildcardTypeAdapterFactory(),
        ]);

        $reader = new JsonDecodeReader('{"key": "value"}');
        $reader->beginObject();
        $reader->nextName();
        $reader->nextString();
        $adapter = new WildcardTypeAdapter($typeAdapterProvider);
        $result = $adapter->read($reader);

        self::assertSame('key', $result);
    }

    public function testSerializeArray()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new ArrayTypeAdapterFactory(),
            new WildcardTypeAdapterFactory(),
        ]);

        $adapter = $typeAdapterProvider->getAdapter(new PhpType('?'));

        self::assertSame('[]', $adapter->writeToJson([], false));
    }

    public function testSerializeObject()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new StringTypeAdapterFactory(),
            new ArrayTypeAdapterFactory(),
            new WildcardTypeAdapterFactory(),
        ]);

        $adapter = $typeAdapterProvider->getAdapter(new PhpType('?'));

        self::assertSame('{"foo":"bar"}', $adapter->writeToJson(['foo' => 'bar'], false));
    }

    public function testSerializeString()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new StringTypeAdapterFactory(),
            new WildcardTypeAdapterFactory(),
        ]);

        $adapter = $typeAdapterProvider->getAdapter(new PhpType('?'));

        self::assertSame('"foo"', $adapter->writeToJson('foo', false));
    }

    public function testSerializeBooleanTrue()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new BooleanTypeAdapterFactory(),
            new WildcardTypeAdapterFactory(),
        ]);

        $adapter = $typeAdapterProvider->getAdapter(new PhpType('?'));

        self::assertSame('true', $adapter->writeToJson(true, false));
    }

    public function testSerializeBooleanFalse()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new BooleanTypeAdapterFactory(),
            new WildcardTypeAdapterFactory(),
        ]);

        $adapter = $typeAdapterProvider->getAdapter(new PhpType('?'));

        self::assertSame('false', $adapter->writeToJson(false, false));
    }

    public function testSerializeInteger()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new IntegerTypeAdapterFactory(),
            new WildcardTypeAdapterFactory(),
        ]);

        $adapter = $typeAdapterProvider->getAdapter(new PhpType('?'));

        self::assertSame('1', $adapter->writeToJson(1, false));
    }

    public function testSerializeNull()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new NullTypeAdapterFactory(),
            new WildcardTypeAdapterFactory(),
        ]);

        $adapter = $typeAdapterProvider->getAdapter(new PhpType('?'));

        self::assertSame('null', $adapter->writeToJson(null, false));
    }

    public function testSerializeResource()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The type "resource" could not be handled by any of the registered type adapters');

        $typeAdapterProvider = new TypeAdapterProvider([
            new NullTypeAdapterFactory(),
            new WildcardTypeAdapterFactory(),
        ]);

        $adapter = $typeAdapterProvider->getAdapter(new PhpType('?'));

        $adapter->writeToJson(fopen(__FILE__, 'rb'), false);
    }
}
