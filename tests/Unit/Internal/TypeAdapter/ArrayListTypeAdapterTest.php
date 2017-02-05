<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter;

use LogicException;
use PHPUnit_Framework_TestCase;
use Tebru\Collection\ArrayList;
use Tebru\Collection\HashMap;
use Tebru\Gson\Internal\PhpType;
use Tebru\Gson\Internal\TypeAdapter\ArrayListTypeAdapter;
use Tebru\Gson\Internal\TypeAdapter\ArrayTypeAdapter;
use Tebru\Gson\Internal\TypeAdapter\Factory\ArrayListTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\FloatTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\HashMapTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\StringTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\WildcardTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapterProvider;

/**
 * Class ArrayListTypeAdapterTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\TypeAdapter\ArrayListTypeAdapter
 */
class ArrayListTypeAdapterTest extends PHPUnit_Framework_TestCase
{
    public function testNull()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new ArrayListTypeAdapterFactory(),
        ]);

        $adapter = $typeAdapterProvider->getAdapter(new PhpType('List'));

        /** @var ArrayList $result */
        $result = $adapter->readFromJson('null');

        self::assertNull($result);
    }

    public function testSimpleArray()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new FloatTypeAdapterFactory(),
            new ArrayListTypeAdapterFactory(),
            new WildcardTypeAdapterFactory(),
        ]);

        /** @var ArrayTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new PhpType('List'));

        /** @var ArrayList $result */
        $result = $adapter->readFromJson('[1, 2, 3]');

        self::assertInstanceOf(ArrayList::class, $result);
        self::assertCount(3, $result);
        self::assertSame(1.0, $result->get(0));
        self::assertSame(2.0, $result->get(1));
        self::assertSame(3.0, $result->get(2));
    }

    public function testNestedArray()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new FloatTypeAdapterFactory(),
            new WildcardTypeAdapterFactory(),
            new ArrayListTypeAdapterFactory(),
        ]);

        /** @var ArrayTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new PhpType('List'));

        /** @var ArrayList $result */
        $result = $adapter->readFromJson('[[1], [2], [3]]');

        self::assertInstanceOf(ArrayList::class, $result);
        self::assertCount(3, $result);
        self::assertInstanceOf(ArrayList::class, $result->get(0));
        self::assertInstanceOf(ArrayList::class, $result->get(1));
        self::assertInstanceOf(ArrayList::class, $result->get(2));
    }

    public function testNestedObject()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new StringTypeAdapterFactory(),
            new FloatTypeAdapterFactory(),
            new ArrayListTypeAdapterFactory(),
            new WildcardTypeAdapterFactory(),
            new HashMapTypeAdapterFactory(),
        ]);

        /** @var ArrayTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new PhpType('List'));

        /** @var ArrayList $result */
        $result = $adapter->readFromJson('[{"key": "value"}]');

        /** @var HashMap $object */
        $object = $result->get(0);

        self::assertInstanceOf(ArrayList::class, $result);
        self::assertCount(1, $result);
        self::assertInstanceOf(HashMap::class, $object);
        self::assertSame('value', $object->get('key'));
    }

    public function testNestedObjectExplicit()
    {
        $typeAdapterProvider = new TypeAdapterProvider([
            new StringTypeAdapterFactory(),
            new FloatTypeAdapterFactory(),
            new ArrayListTypeAdapterFactory(),
            new WildcardTypeAdapterFactory(),
            new HashMapTypeAdapterFactory(),
        ]);

        /** @var ArrayTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new PhpType('List<Map>'));

        /** @var ArrayList $result */
        $result = $adapter->readFromJson('[{"key": "value"}]');

        /** @var HashMap $object */
        $object = $result->get(0);

        self::assertInstanceOf(ArrayList::class, $result);
        self::assertCount(1, $result);
        self::assertInstanceOf(HashMap::class, $object);
        self::assertSame('value', $object->get('key'));
    }

    public function testTooManyGenerics()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('ArrayList expected to have exactly one generic type');

        $adapter = new ArrayListTypeAdapter(new PhpType('ArrayList<Foo, Bar>'), new TypeAdapterProvider([]));
        $adapter->readFromJson('[1]');
    }
}
