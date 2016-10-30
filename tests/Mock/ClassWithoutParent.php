<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock;

use Tebru\Gson\Test\Mock\Annotation\BarAnnotation;
use Tebru\Gson\Test\Mock\Annotation\BazAnnotation;
use Tebru\Gson\Test\Mock\Annotation\FooAnnotation;

/**
 * Class ClassWithoutParent
 *
 * @author Nate Brunette <n@tebru.net>
 *
 * @FooAnnotation("foo3")
 * @BazAnnotation("baz")
 */
class ClassWithoutParent
{
    /**
     * @FooAnnotation("foo")
     * @BarAnnotation("bar")
     */
    private $foo;

    /**
     * @FooAnnotation("foo2")
     */
    private $bar;
    protected $baz;
    public $qux;
}
