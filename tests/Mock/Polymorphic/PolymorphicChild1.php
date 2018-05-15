<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
declare(strict_types=1);

namespace Tebru\Gson\Test\Mock\Polymorphic;

/**
 * Class PolymorphicChild1
 *
 * @author Nate Brunette <n@tebru.net>
 */
class PolymorphicChild1 extends Base
{
    private $nested;

    /**
     * @return Base
     */
    public function getNested(): Base
    {
        return $this->nested;
    }
}
