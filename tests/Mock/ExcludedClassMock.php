<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock;

use Tebru\Gson\Annotation\Exclude;
use Tebru\Gson\Annotation\Type;

/**
 * Class ExcludedClassMock
 *
 * @author Nate Brunette <n@tebru.net>
 * @Exclude()
 */
class ExcludedClassMock
{
    /**
     * @Type("Tebru\Gson\Test\Mock\GsonMock")
     */
    private $foo;
    private $bar;
}
