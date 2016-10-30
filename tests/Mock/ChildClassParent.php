<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock;

use Tebru\Gson\Test\Mock\Annotation\BarAnnotation;
use Tebru\Gson\Test\Mock\Annotation\BazAnnotation;
use Tebru\Gson\Test\Mock\Annotation\FooAnnotation;
use Tebru\Gson\Test\Mock\Annotation\QuxAnnotation;

/**
 * Class ChildClassParent
 *
 * @author Nate Brunette <n@tebru.net>
 *
 * @BarAnnotation("bar2")
 * @BazAnnotation("baz2")
 */
class ChildClassParent extends ChildClassParent2
{
    /**
     * @FooAnnotation("foo4")
     * @QuxAnnotation("qux")
     */
    private $foo;
    private $bar;
    protected $baz;
}
