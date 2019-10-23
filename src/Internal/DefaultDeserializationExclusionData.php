<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal;

use Tebru\Gson\Context\ReaderContext;
use Tebru\Gson\Exclusion\DeserializationExclusionData;

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
     * @var ReaderContext
     */
    private $context;

    /**
     * Constructor
     *
     * @param object $objectToReadInto
     * @param ReaderContext $context
     */
    public function __construct($objectToReadInto, ReaderContext $context)
    {
        $this->objectToReadInto = $objectToReadInto;
        $this->context = $context;
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
        return $this->context;
    }
}
