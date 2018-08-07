<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Exclusion;

/**
 * Interface SerializationExclusionData
 *
 * Runtime information available during serialization
 *
 * @author Nate Brunette <n@tebru.net>
 */
interface SerializationExclusionData
{
    /**
     * Get the object currently being serialized
     *
     * @return object
     */
    public function getObjectToSerialize();

    /**
     * Get the current path formatted as json xpath
     *
     * @return string
     */
    public function getPath(): string;
}
