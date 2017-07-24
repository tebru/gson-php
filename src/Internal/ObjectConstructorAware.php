<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal;

/**
 * Interface ObjectConstructorAware
 *
 * Used on [@see TypeAdapter]s that construct objects
 *
 * @author Nate Brunette <n@tebru.net>
 */
interface ObjectConstructorAware
{
    /**
     * Set the object constructor
     *
     * @param ObjectConstructor $objectConstructor
     * @return void
     */
    public function setObjectConstructor(ObjectConstructor $objectConstructor): void;
}
