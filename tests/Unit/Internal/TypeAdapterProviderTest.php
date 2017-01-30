<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
namespace Tebru\Gson\Test\Unit\Internal;

use InvalidArgumentException;
use PHPUnit_Framework_TestCase;
use Tebru\Gson\Internal\PhpType;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\Test\Mock\TypeAdapterMock;

/**
 * Class TypeAdapterProviderTest
 *
 * @author Nate Brunette <n@tebru.net>
 */
class TypeAdapterProviderTest extends PHPUnit_Framework_TestCase
{
    public function testGetTypeAdapter()
    {
        $provider = new TypeAdapterProvider([new TypeAdapterMock()]);
        $adapter = $provider->getAdapter(new PhpType('string'));

        self::assertInstanceOf(TypeAdapterMock::class, $adapter);
    }

    public function testGetTypeAdapterThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The type "foo" could not be handled by any of the registered type adapters');

        $provider = new TypeAdapterProvider([new TypeAdapterMock()]);
        $provider->getAdapter(new PhpType('foo'));
    }

    public function testGetTypeAdapterSkipClass()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The type "string" could not be handled by any of the registered type adapters');

        $provider = new TypeAdapterProvider([new TypeAdapterMock()]);
        $provider->getAdapter(new PhpType('string'), TypeAdapterMock::class);
    }

    public function testGetTypeAdapterMultiple()
    {
        $provider = new TypeAdapterProvider([new TypeAdapterMock()]);
        $adapter = $provider->getAdapter(new PhpType('string'));
        $adapter2 = $provider->getAdapter(new PhpType('string'));

        self::assertSame($adapter, $adapter2);
    }
}
