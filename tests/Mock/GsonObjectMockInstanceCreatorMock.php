<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock;

use Tebru\Gson\InstanceCreator;
use Tebru\Gson\Internal\PhpType;

/**
 * Class GsonObjectMockInstanceCreatorMock
 *
 * @author Nate Brunette <n@tebru.net>
 */
class GsonObjectMockInstanceCreatorMock implements InstanceCreator
{
    /**
     * Accepts a [@see PhpType] and returns an instantiated object
     *
     * @param PhpType $phpType
     * @return object
     */
    public function createInstance(PhpType $phpType)
    {
        return new GsonObjectMock(null);
    }
}
