<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson;

/**
 * Interface ExclusionData
 *
 * Contains data that is only available at runtime that is passed to exclusion strategies
 *
 * @author Nate Brunette <n@tebru.net>
 */
interface ExclusionData
{
    /**
     * Returns true if the data is available during serialization
     *
     * @return bool
     */
    public function isSerialize(): bool;

    /**
     * This will either contain a hydrated object during serialization or the instantiated
     * object during deserialization.  During deserialization, this object will likely be
     * empty unless a hydrated object was provided.
     *
     * @return object
     */
    public function getData();

    /**
     * During deserialization, this will return the provided json after json_decode. During
     * serialization, this will return null
     *
     * @return mixed|null
     */
    public function getDeserializePayload();
}
