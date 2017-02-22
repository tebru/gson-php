<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock\Unit\Internal\Data\PhpType;

use Countable;

/**
 * Class PhpTypeClassWithInterface
 *
 * @author Nate Brunette <n@tebru.net>
 */
class PhpTypeClassWithInterface extends PhpTypeClassParent implements Countable
{
    public function count() {}
}
