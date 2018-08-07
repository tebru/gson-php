<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal;

use Tebru\Gson\ExclusionData;

/**
 * Class DefaultExclusionData
 *
 * @author Nate Brunette <n@tebru.net>
 * @deprecated
 */
final class DefaultExclusionData implements ExclusionData
{
    /**
     * If the data is available during serialization or deserialization
     *
     * @var bool
     */
    private $serialize;

    /**
     * The object to be serialized or deserialized into
     *
     * @var object
     */
    private $data;

    /**
     * The original payload available during deserialization
     *
     * @var mixed|null
     */
    private $payload;

    /**
     * Constructor
     *
     * @param bool $serialize
     * @param object $data
     * @param mixed|null $payload
     */
    public function __construct(bool $serialize, $data, $payload = null)
    {
        @trigger_error('Gson: \Tebru\Gson\ExclusionData is deprecated since v0.6.0 and will be removed in v0.7.0. Use on of \Tebru\Gson\Exclusion\*ExclusionData instead.', E_USER_DEPRECATED);

        $this->serialize = $serialize;
        $this->data = $data;
        $this->payload = $payload;
    }

    /**
     * Returns true if the data is available during serialization
     *
     * @return bool
     * @deprecated
     */
    public function isSerialize(): bool
    {
        @trigger_error('Gson: \Tebru\Gson\ExclusionData is deprecated since v0.6.0 and will be removed in v0.7.0. Use on of \Tebru\Gson\Exclusion\*ExclusionData instead.', E_USER_DEPRECATED);

        return $this->serialize;
    }

    /**
     * This will either contain a hydrated object during serialization or the instantiated
     * object during deserialization.  During deserialization, this object will likely be
     * empty unless a hydrated object was provided.
     *
     * @return object
     */
    public function getData()
    {
        @trigger_error('Gson: \Tebru\Gson\ExclusionData is deprecated since v0.6.0 and will be removed in v0.7.0. Use on of \Tebru\Gson\Exclusion\*ExclusionData instead.', E_USER_DEPRECATED);

        return $this->data;
    }

    /**
     * During deserialization, this will return the provided json after json_decode. During
     * serialization, this will return null
     *
     * @return mixed|null
     */
    public function getDeserializePayload()
    {
        @trigger_error('Gson: \Tebru\Gson\ExclusionData is deprecated since v0.6.0 and will be removed in v0.7.0. Use on of \Tebru\Gson\Exclusion\*ExclusionData instead.', E_USER_DEPRECATED);

        return $this->payload;
    }
}
