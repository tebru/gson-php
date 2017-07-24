<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson;

use Tebru\PhpType\TypeToken;

/**
 * Interface InstanceCreator
 *
 * Used to define a custom way to instantiate a class that requires
 * arguments
 *
 * @author Nate Brunette <n@tebru.net>
 */
interface InstanceCreator
{
    /**
     * Accepts a [@see PhpType] and returns an instantiated object
     *
     * @param TypeToken $type
     * @return object
     */
    public function createInstance(TypeToken $type);
}
