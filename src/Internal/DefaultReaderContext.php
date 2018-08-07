<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
declare(strict_types=1);

namespace Tebru\Gson\Internal;

use Tebru\Gson\ReaderContext;

/**
 * Class JsonReaderContext
 *
 * Runtime context that can be used during reading
 *
 * @author Nate Brunette <n@tebru.net>
 */
class DefaultReaderContext implements ReaderContext
{
    /**
     * True if we're reading into an existing object
     *
     * @var bool
     */
    private $usesExistingObject = false;

    /**
     * If we're reading into an existing object
     *
     * @return bool
     */
    public function usesExistingObject(): bool
    {
        return $this->usesExistingObject;
    }

    /**
     * When deserializing into an existing object
     *
     * @param bool $usesExistingObject
     * @return ReaderContext
     */
    public function setUsesExistingObject(bool $usesExistingObject): ReaderContext
    {
        $this->usesExistingObject = $usesExistingObject;

        return $this;
    }
}
