<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Exclusion;

use Tebru\Gson\ReaderContext;

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
     * Get the json data after json_decode()
     *
     * @return mixed
     */
    public function getPayload();

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

    /**
     * Get the current path formatted as json xpath
     *
     * @return string
     */
    public function getPath(): string;
}
