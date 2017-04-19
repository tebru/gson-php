<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter;

use PHPUnit_Framework_TestCase;
use Tebru\Gson\Internal\Excluder;
use Tebru\Gson\Internal\TypeAdapter\ExcluderTypeAdapter;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\Test\Mock\ExcluderExcludeDeserializeMock;
use Tebru\Gson\Test\Mock\ExcluderExcludeSerializeMock;
use Tebru\Gson\Test\MockProvider;
use Tebru\PhpType\TypeToken;

/**
 * Class ExcluderTypeAdapterTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\TypeAdapter\ExcluderTypeAdapter
 * @covers \Tebru\Gson\TypeAdapter
 */
class ExcluderTypeAdapterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Excluder
     */
    private $excluder;

    /**
     * @var TypeAdapterProvider
     */
    private $typeAdapterProvider;

    /**
     * Set up test dependencies
     */
    public function setUp()
    {
        $this->excluder = new Excluder();
        $this->typeAdapterProvider = MockProvider::typeAdapterProvider($this->excluder);
    }

    public function testDeserializeSkips()
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken(ExcluderExcludeDeserializeMock::class));

        self::assertNull($adapter->readFromJson('{}'));
    }

    public function testDeserializeDelegates()
    {
        $this->excluder->setVersion('2');

        /** @var ExcluderTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken(ExcluderExcludeSerializeMock::class));

        self::assertEquals(new ExcluderExcludeSerializeMock(), $adapter->readFromJson('{"foo": null}'));
    }

    public function testSerializeSkips()
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken(ExcluderExcludeSerializeMock::class));

        self::assertSame('null', $adapter->writeToJson(new ExcluderExcludeSerializeMock(), false));
    }

    public function testSerializeDelegates()
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new TypeToken(ExcluderExcludeDeserializeMock::class));

        self::assertSame('{}', $adapter->writeToJson(new ExcluderExcludeDeserializeMock(), false));
    }
}
