<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson;

use Tebru\Gson\Element\JsonObject;

/**
 * Interface Discriminator
 *
 * This interface is used to enable polymorphic deserialization. Given json data, it returns
 * a class that should be deserialized into.
 *
 * @author Nate Brunette <n@tebru.net>
 */
interface Discriminator
{
    /**
     * Returns a classname based on data provided in a [@see JsonObject]
     *
     * @param JsonObject $object
     * @return string
     */
    public function getClass(JsonObject $object): string;
}
