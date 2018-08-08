<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Exclusion;

use Tebru\Gson\Cacheable;

/**
 * Interface ExclusionStrategy
 *
 * @author Nate Brunette <n@tebru.net>
 */
interface ExclusionStrategy extends Cacheable
{
    /**
     * Return true if the result of the strategy should be cached
     *
     * @return bool
     */
    public function shouldCache(): bool;
}
