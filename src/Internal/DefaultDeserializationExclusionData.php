<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal;

use Tebru\Gson\Exclusion\DeserializationExclusionData;
use Tebru\Gson\JsonReadable;
use Tebru\Gson\ReaderContext;

/**
 * Class DefaultDeserializationExclusionData
 *
 * @author Nate Brunette <n@tebru.net>
 */
class DefaultDeserializationExclusionData implements DeserializationExclusionData
{
    /**
     * @var object
     */
    private $objectToReadInto;

    /**
     * @var JsonReadable
     */
    private $reader;

    /**
     * Constructor
     *
     * @param object $objectToReadInto
     * @param JsonReadable $reader
     */
    public function __construct($objectToReadInto, JsonReadable $reader)
    {
        $this->objectToReadInto = $objectToReadInto;
        $this->reader = $reader;
    }

    /**
     * Get the json data after json_decode()
     *
     * @return mixed
     */
    public function getPayload()
    {
        return $this->reader->getPayload();
    }

    /**
     * Returns the initial object if it was provided to Gson::fromJson() or null
     *
     * @return object|null
     */
    public function getObjectToReadInto()
    {
        return $this->objectToReadInto;
    }

    /**
     * Get the reader context
     *
     * @return ReaderContext
     */
    public function getContext(): ReaderContext
    {
        return $this->reader->getContext();
    }

    /**
     * Get the current path formatted as json xpath
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->reader->getPath();
    }
}
