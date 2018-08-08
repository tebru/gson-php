<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Exclusion;

/**
 * Interface SerializationExclusionDataAware
 *
 * Provides access to [@see SerializationExclusionData]. Implementing this interface will trigger a call during
 * runtime. Typically, this method will only be called once per object, and the state will be internally mutated
 * by the library.
 *
 * @author Nate Brunette <n@tebru.net>
 */
interface SerializationExclusionDataAware
{
    /**
     * Sets the serialization exclusion data
     *
     * @param SerializationExclusionData $data
     * @return void
     */
    public function setSerializationExclusionData(SerializationExclusionData $data): void;
}
