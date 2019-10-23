<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
declare(strict_types=1);


namespace Tebru\Gson\Test\Unit\Context;

use PHPUnit\Framework\TestCase;
use Tebru\Gson\Context\ReaderContext;
use Tebru\Gson\Context\WriterContext;

/**
 * Class ContextTest
 *
 * @author Nate Brunette <n@tebru.net>
 */
class ContextTest extends TestCase
{
    public function testReaderContext(): void
    {
        $context = new ReaderContext();
        $context->setPayload([]);
        $context->setUsesExistingObject(true);
        $context->setAttribute('foo', 'bar');
        $context->setEnableScalarAdapters(false);

        self::assertFalse($context->enableScalarAdapters());
        self::assertTrue($context->usesExistingObject());
        self::assertSame([], $context->getPayload());
        self::assertSame(['foo' => 'bar'], $context->getAttributes());
        self::assertSame('bar', $context->getAttribute('foo'));
    }

    public function testWriterContext(): void
    {
        $context = new WriterContext();
        $context->setAttribute('foo', 'bar');
        $context->setSerializeNull(true);
        $context->setEnableScalarAdapters(false);

        self::assertFalse($context->enableScalarAdapters());
        self::assertTrue($context->serializeNull());
        self::assertSame(['foo' => 'bar'], $context->getAttributes());
        self::assertSame('bar', $context->getAttribute('foo'));
    }
}
