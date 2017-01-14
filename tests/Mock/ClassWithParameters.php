<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock;

/**
 * Class ClassWithParameters
 *
 * @author Nate Brunette <n@tebru.net>
 */
class ClassWithParameters
{
    public $class;

    public function __construct(string $class)
    {
        $this->class = $class;
    }
}
