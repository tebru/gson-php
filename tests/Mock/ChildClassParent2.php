<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock;

use Tebru\Gson\Test\Mock\Annotation\QuxAnnotation;

/**
 * Class ChildClassParent2
 *
 * @author Nate Brunette <n@tebru.net>
 */
class ChildClassParent2
{
    /**
     * @QuxAnnotation("qux")
     */
    public $qux;
    public $overridden;

    public function getOverridden()
    {
        return $this->overridden;
    }

    public function setOverridden($value)
    {
        $this->overridden = $value;
    }
}
