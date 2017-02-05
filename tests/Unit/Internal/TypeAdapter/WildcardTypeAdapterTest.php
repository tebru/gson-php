<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter;

use PHPUnit_Framework_TestCase;
use Tebru\Gson\Exception\UnexpectedJsonTokenException;
use Tebru\Gson\Internal\JsonDecodeReader;
use Tebru\Gson\Internal\PhpType;
use Tebru\Gson\Internal\TypeAdapter\Factory\ArrayTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\BooleanTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\FloatTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\NullTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\StringTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\WildcardTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\WildcardTypeAdapter;
use Tebru\Gson\Internal\TypeAdapterProvider;

/**
 * Class WildcardTypeAdapterTest
 *
 * @author Nate Brunette <n@tebru.net>
 */
class WildcardTypeAdapterTest extends PHPUnit_Framework_TestCase
{
    public function testArray()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new ArrayTypeAdapterFactory(),
            new WildcardTypeAdapterFactory(),
        ]);

        $adapter = $typeAdapterProvider->getAdapter(new PhpType('?'));

        $result = $adapter->readFromJson('[]');

        self::assertSame([], $result);
    }

    public function testObject()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new ArrayTypeAdapterFactory(),
            new WildcardTypeAdapterFactory(),
        ]);

        $adapter = $typeAdapterProvider->getAdapter(new PhpType('?'));

        $result = $adapter->readFromJson('{}');

        self::assertSame([], $result);
    }

    public function testString()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new StringTypeAdapterFactory(),
            new WildcardTypeAdapterFactory(),
        ]);

        $adapter = $typeAdapterProvider->getAdapter(new PhpType('?'));

        $result = $adapter->readFromJson('"foo"');

        self::assertSame('foo', $result);
    }

    public function testName()
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

    public function testBoolean()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new BooleanTypeAdapterFactory(),
            new WildcardTypeAdapterFactory(),
        ]);

        $adapter = $typeAdapterProvider->getAdapter(new PhpType('?'));

        $result = $adapter->readFromJson('true');

        self::assertTrue($result);
    }

    public function testBooleanFalse()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new BooleanTypeAdapterFactory(),
            new WildcardTypeAdapterFactory(),
        ]);

        $adapter = $typeAdapterProvider->getAdapter(new PhpType('?'));

        $result = $adapter->readFromJson('false');

        self::assertFalse($result);
    }

    public function testNumberInt()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new FloatTypeAdapterFactory(),
            new WildcardTypeAdapterFactory(),
        ]);

        $adapter = $typeAdapterProvider->getAdapter(new PhpType('?'));

        $result = $adapter->readFromJson('1');

        self::assertSame(1.0, $result);
    }

    public function testNumberFloat()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new FloatTypeAdapterFactory(),
            new WildcardTypeAdapterFactory(),
        ]);

        $adapter = $typeAdapterProvider->getAdapter(new PhpType('?'));

        $result = $adapter->readFromJson('1.1');

        self::assertSame(1.1, $result);
    }

    public function testNumberNull()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new NullTypeAdapterFactory(),
            new WildcardTypeAdapterFactory(),
        ]);

        $adapter = $typeAdapterProvider->getAdapter(new PhpType('?'));

        $result = $adapter->readFromJson('null');

        self::assertNull($result);
    }

    public function testException()
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
}
