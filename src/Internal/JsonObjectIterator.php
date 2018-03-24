<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal;

use Tebru\Gson\Element\JsonObject;

/**
 * Class JsonObjectIterator
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class JsonObjectIterator extends AbstractIterator
{
    /**
     * Constructor
     *
     * @param JsonObject $jsonObject
     */
    public function __construct(JsonObject $jsonObject)
    {
        foreach ($jsonObject as $key => $value) {
            $this->queue[] = [$key, $value];
            $this->total++;
        }
    }
}
