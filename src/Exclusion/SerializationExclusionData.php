<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Exclusion;

use Tebru\Gson\Context\WriterContext;

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
     * Get the writer context
     *
     * @return WriterContext
     */
    public function getContext(): WriterContext;
}
