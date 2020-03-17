<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock;

use Tebru\Gson\InstanceCreator;
use Tebru\PhpType\TypeToken;

/**
 * Class ClassWithParametersInstanceCreator
 *
 * @author Nate Brunette <n@tebru.net>
 */
class ClassWithParametersInstanceCreator implements InstanceCreator
{
    /**
     * Accepts a [@see TypeToken] and returns an instantiated object
     *
     * @param TypeToken $type
     * @return mixed
     */
    public function createInstance(TypeToken $type)
    {
        return new ClassWithParameters($type->getRawType());
    }

    /**
     * Return true if object can be written to disk
     *
     * @return bool
     */
    public function canCache(): bool
    {
        return true;
    }
}
