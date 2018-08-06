<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson;

/**
 * Class JsonReaderContext
 *
 * Runtime context that can be used during reading
 *
 * @author Nate Brunette <n@tebru.net>
 */
interface ReaderContext
{
    /**
     * If we're reading into an existing object
     *
     * @return bool
     */
    public function usesExistingObject(): bool;
}
