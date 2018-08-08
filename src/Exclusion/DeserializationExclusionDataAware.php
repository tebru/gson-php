<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Exclusion;

/**
 * Interface DeserializationExclusionDataAware
 *
 * Provides access to [@see DeserializationExclusionData]. Implementing this interface will trigger a call during
 * runtime. Typically, this method will only be called once per object, and the state will be internally mutated
 * by the library.
 *
 * @author Nate Brunette <n@tebru.net>
 */
interface DeserializationExclusionDataAware
{
    /**
     * Sets the deserialization exclusion data
     *
     * @param DeserializationExclusionData $data
     * @return void
     */
    public function setDeserializationExclusionData(DeserializationExclusionData $data): void;
}
