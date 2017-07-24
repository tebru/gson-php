<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal;

/**
 * Interface GetterStrategy
 *
 * Allows getting data from an object.  Each implementation should be constructed
 * with the necessary information to get the data from the object.  There should be a new
 * strategy created for each type of data required.
 *
 * @author Nate Brunette <n@tebru.net>
 */
interface GetterStrategy
{
    /**
     * Returns the specific data from a provided object
     *
     * @param object $object
     * @return mixed
     */
    public function get($object);
}
