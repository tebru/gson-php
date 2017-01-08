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

    private $withTypehint;

    public function getBaz()
    {
        return $this->baz;
    }

    public function setBaz($baz)
    {
        $this->baz = $baz;
    }

    public function baz()
    {
        return $this->baz;
    }

    public function set_baz($baz)
    {
        $this->baz = $baz;
    }

    protected function isFoo()
    {
        return $this->foo;
    }

    private function setFoo($foo = 'bar')
    {
        $this->foo = $foo;
    }

    public function getWithReturnType(): ChildClass
    {
        return $this->withTypehint;
    }

    public function setWithTypehint(ChildClass $childClass)
    {
        $this->withTypehint = $childClass;
    }
}
