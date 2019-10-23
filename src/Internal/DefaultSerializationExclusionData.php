<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal;

use Tebru\Gson\Context\WriterContext;
use Tebru\Gson\Exclusion\SerializationExclusionData;

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
     * @var WriterContext
     */
    private $context;

    /**
     * Constructor
     *
     * @param object $objectToSerialize
     * @param WriterContext $context
     */
    public function __construct($objectToSerialize, WriterContext $context)
    {
        $this->objectToSerialize = $objectToSerialize;
        $this->context = $context;
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
     * Get the writer context
     *
     * @return WriterContext
     */
    public function getContext(): WriterContext
    {
        return $this->context;
    }
}
