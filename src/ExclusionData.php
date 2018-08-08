<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson;

// @codeCoverageIgnoreStart
@trigger_error('Gson: \Tebru\Gson\ExclusionData is deprecated since v0.6.0 and will be removed in v0.7.0. Use one of \Tebru\Gson\Exclusion\*ExclusionData instead.', E_USER_DEPRECATED);
// @codeCoverageIgnoreEnd

/**
 * Interface ExclusionData
 *
 * Contains data that is only available at runtime that is passed to exclusion strategies
 *
 * @author Nate Brunette <n@tebru.net>
 * @deprecated Since v0.6.0 to be removed in v0.7.0. Use one of \Tebru\Gson\Exclusion\*ExclusionData instead instead.
 */
interface ExclusionData
{
    /**
     * Returns true if the data is available during serialization
     *
     * @return bool
     * @deprecated Since v0.6.0 to be removed in v0.7.0. Use one of \Tebru\Gson\Exclusion\*ExclusionData instead instead.
     */
    public function isSerialize(): bool;

    /**
     * This will either contain a hydrated object during serialization or the instantiated
     * object during deserialization.  During deserialization, this object will likely be
     * empty unless a hydrated object was provided.
     *
     * @return object
     * @deprecated Since v0.6.0 to be removed in v0.7.0. Use one of \Tebru\Gson\Exclusion\*ExclusionData instead instead.
     */
    public function getData();

    /**
     * During deserialization, this will return the provided json after json_decode. During
     * serialization, this will return null
     *
     * @return mixed|null
     * @deprecated Since v0.6.0 to be removed in v0.7.0. Use one of \Tebru\Gson\Exclusion\*ExclusionData instead instead.
     */
    public function getDeserializePayload();
}
