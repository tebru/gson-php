<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
namespace Tebru\Gson\Test\Unit\Internal;

use PHPUnit_Framework_TestCase;
use Tebru\Gson\Element\JsonObject;
use Tebru\Gson\Element\JsonPrimitive;
use Tebru\Gson\Internal\JsonObjectIterator;

/**
 * Class JsonObjectIteratorTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\JsonObjectIterator
 * @covers \Tebru\Gson\Internal\AbstractIterator
 */
class JsonObjectIteratorTest extends PHPUnit_Framework_TestCase
{
    public function testStartsAtBeginning()
    {
        $jsonObject = new JsonObject();
        $jsonObject->addInteger('foo', 1);
        $jsonObject->addInteger('bar', 2);

        $iterator = new JsonObjectIterator($jsonObject);

        self::assertSame('foo', $iterator->key());
    }

    public function testNext()
    {
        $jsonObject = new JsonObject();
        $jsonObject->addInteger('foo', 1);
        $jsonObject->addInteger('bar', 2);

        $iterator = new JsonObjectIterator($jsonObject);
        $iterator->next();

        self::assertSame('bar', $iterator->key());
    }

    public function testCurrent()
    {
        $jsonObject = new JsonObject();
        $jsonObject->addInteger('foo', 1);
        $jsonObject->addInteger('bar', 2);

        $iterator = new JsonObjectIterator($jsonObject);
        $iterator->next();

        self::assertEquals(JsonPrimitive::create(2), $iterator->current());
    }

    public function testValid()
    {
        $jsonObject = new JsonObject();
        $jsonObject->addInteger('foo', 1);
        $jsonObject->addInteger('bar', 2);

        $iterator = new JsonObjectIterator($jsonObject);
        $iterator->next();

        self::assertTrue($iterator->valid());
    }

    public function testValidFalse()
    {
        $jsonObject = new JsonObject();
        $jsonObject->addInteger('foo', 1);
        $jsonObject->addInteger('bar', 2);

        $iterator = new JsonObjectIterator($jsonObject);
        $iterator->next();
        $iterator->next();

        self::assertFalse($iterator->valid());
    }

    public function testValidBeginning()
    {
        $jsonObject = new JsonObject();
        $jsonObject->addInteger('foo', 1);
        $jsonObject->addInteger('bar', 2);

        $iterator = new JsonObjectIterator($jsonObject);

        self::assertTrue($iterator->valid());
    }

    public function testRewind()
    {
        $jsonObject = new JsonObject();
        $jsonObject->addInteger('foo', 1);
        $jsonObject->addInteger('bar', 2);

        $iterator = new JsonObjectIterator($jsonObject);
        $iterator->next();
        $iterator->next();
        $iterator->rewind();

        self::assertSame('foo', $iterator->key());
    }
}
