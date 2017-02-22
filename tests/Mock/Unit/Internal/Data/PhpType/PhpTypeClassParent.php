<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock\Unit\Internal\Data\PhpType;

/**
 * Class PhpTypeClassParent
 *
 * @author Nate Brunette <n@tebru.net>
 */
abstract class PhpTypeClassParent implements PhpTypeInterface
{
    public function getIterator() {}
    public function offsetExists($offset) {}
    public function offsetGet($offset) {}
    public function offsetSet($offset, $value) {}
    public function offsetUnset($offset) {}
}
