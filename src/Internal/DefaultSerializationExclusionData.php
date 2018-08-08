<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal;

use Tebru\Gson\Exclusion\SerializationExclusionData;
use Tebru\Gson\JsonWritable;

/**
 * Class DefaultSerializationExclusionData
 *
 * @author Nate Brunette <n@tebru.net>
 */
class DefaultSerializationExclusionData implements SerializationExclusionData
{
    /**
     * The object that's currently being serialized
     *
     * @var object
     */
    private $objectToSerialize;

    /**
     * Instance of the current writer
     *
     * @var JsonWritable
     */
    private $writer;

    /**
     * Constructor
     *
     * @param object $objectToSerialize
     * @param JsonWritable $writer
     */
    public function __construct($objectToSerialize, JsonWritable $writer)
    {
        $this->objectToSerialize = $objectToSerialize;
        $this->writer = $writer;
    }

    /**
     * Get the object currently being serialized
     *
     * @return object
     */
    public function getObjectToSerialize()
    {
        return $this->objectToSerialize;
    }

    /**
     * Get the current path formatted as json xpath
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->writer->getPath();
    }
}
