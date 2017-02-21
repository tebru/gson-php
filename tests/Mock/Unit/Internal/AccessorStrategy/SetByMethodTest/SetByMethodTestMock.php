<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock\Unit\Internal\AccessorStrategy\SetByMethodTest;

/**
 * Class SetByMethodTestMock
 *
 * @author Nate Brunette <n@tebru.net>
 */
class SetByMethodTestMock
{
    public $foo;
    public function setFoo($foo) {
        $this->foo = $foo;
    }
}
