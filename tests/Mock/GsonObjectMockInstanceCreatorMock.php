<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock;

use Tebru\Gson\InstanceCreator;
use Tebru\PhpType\TypeToken;

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
     * @param TypeToken $type
     * @return object
     */
    public function createInstance(TypeToken $type)
    {
        return new GsonObjectMock(null);
    }
}
