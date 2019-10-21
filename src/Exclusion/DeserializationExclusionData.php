<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Exclusion;

use Tebru\Gson\Context\ReaderContext;

/**
 * Interface DeserializationExclusionData
 *
 * Runtime information available during deserialization
 *
 * @author Nate Brunette <n@tebru.net>
 */
interface DeserializationExclusionData
{
    /**
     * Returns the initial object if it was provided to Gson::fromJson() or null
     *
     * @return object|null
     */
    public function getObjectToReadInto();

    /**
     * Get the reader context
     *
     * @return ReaderContext
     */
    public function getContext(): ReaderContext;
}
