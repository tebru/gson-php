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
        $this->serialize = $serialize;
        $this->data = $data;
        $this->payload = $payload;
    }

    /**
     * Returns true if the data is available during serialization
     *
     * @return bool
     */
    public function isSerialize(): bool
    {
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
        return $this->payload;
    }
}
