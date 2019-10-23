<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
declare(strict_types=1);

namespace Tebru\Gson\Context;

/**
 * Class ReaderContext
 *
 * Runtime context that can be used during reading
 *
 * @author Nate Brunette <n@tebru.net>
 */
class ReaderContext extends Context
{
    /**
     * True if we're reading into an existing object
     *
     * @var bool
     */
    private $usesExistingObject = false;

    /**
     * The initial json_decode'd payload
     *
     * @var mixed
     */
    private $payload;

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

    /**
     * @return mixed
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @param mixed $payload
     * @return ReaderContext
     */
    public function setPayload($payload): ReaderContext
    {
        $this->payload = $payload;

        return $this;
    }
}
