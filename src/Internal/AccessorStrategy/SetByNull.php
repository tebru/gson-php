<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal\AccessorStrategy;

use Tebru\Gson\Internal\SetterStrategy;

/**
 * Class SetByNull
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class SetByNull implements SetterStrategy
{
    /**
     * Set value to object
     *
     * @param object $object
     * @param mixed $value
     * @return void
     */
    public function set($object, $value): void
    {
        // noop
    }
}
