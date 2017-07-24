<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal;

/**
 * Interface ObjectConstructor
 *
 * This represents a strategy for creating instances of objects.  Implement
 * this interface to define a custom instantiation strategy.
 *
 * @author Nate Brunette <n@tebru.net>
 */
interface ObjectConstructor
{
    /**
     * Returns the instantiated object
     *
     * @return mixed
     */
    public function construct();
}
