<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal;

/**
 * Interface SetterStrategy
 *
 * Allows setting data to an object.  Each implementation should be constructed
 * with the necessary information to set the data to the object.  There should be a new
 * strategy created for each type of data required.
 *
 * @author Nate Brunette <n@tebru.net>
 */
interface SetterStrategy
{
    /**
     * Set value to object
     *
     * @param object $object
     * @param mixed $value
     * @return void
     */
    public function set($object, $value): void;
}
