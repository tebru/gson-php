<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal;

/**
 * Class ObjectConstructorAwareTrait
 *
 * @author Nate Brunette <n@tebru.net>
 */
trait ObjectConstructorAwareTrait
{
    /**
     * The object constructor instance
     *
     * @var ObjectConstructor
     */
    protected $objectConstructor;

    /**
     * Set the object constructor
     *
     * @param ObjectConstructor $objectConstructor
     */
    public function setObjectConstructor(ObjectConstructor $objectConstructor): void
    {
        $this->objectConstructor = $objectConstructor;
    }
}
