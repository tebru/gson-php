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
 * Class ChildClass
 *
 * @author Nate Brunette <n@tebru.net>
 *
 * @FooAnnotation("foo3")
 * @BazAnnotation("baz")
 */
class ChildClass extends ChildClassParent
{
    /**
     * @FooAnnotation("foo")
     * @BarAnnotation("bar")
     */
    private $foo;

    /**
     * @FooAnnotation("foo2")
     */
    public $overridden;
}
