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
        $this->readerContext = new ReaderContext();
        $this->writerContext = new WriterContext();
    }
}
