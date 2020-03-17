<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
declare(strict_types=1);

namespace Tebru\Gson\Test\Unit\TypeAdapter;

use PHPUnit\Framework\TestCase;
use Tebru\Gson\Context\ReaderContext;
use Tebru\Gson\Context\WriterContext;
use Tebru\Gson\Test\MockProvider;

/**
 * Class TypeAdapterTestCase
 *
 * @author Nate Brunette <n@tebru.net>
 */
class TypeAdapterTestCase extends TestCase
{
    /**
     * @var ReaderContext
     */
    protected $readerContext;

    /**
     * @var WriterContext
     */
    protected $writerContext;

    public function setUp()
    {
        $provider = MockProvider::typeAdapterProvider();
        $this->readerContext = new ReaderContext();
        $this->readerContext->setTypeAdapterProvider($provider);
        $this->readerContext->setExcluder(MockProvider::excluder());
        $this->writerContext = new WriterContext();
        $this->writerContext->setTypeAdapterProvider($provider);
        $this->writerContext->setExcluder(MockProvider::excluder());
    }
}
