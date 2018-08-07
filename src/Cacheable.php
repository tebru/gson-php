<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson;

/**
 * Interface Cacheable
 *
 * Represents that the class is cacheable. This can mean different things for different classes.
 *
 * @author Nate Brunette <n@tebru.net>
 */
interface Cacheable
{
    /**
     * Return true if caching should be enabled
     *
     * @return bool
     */
    public function shouldCache(): bool;
}
