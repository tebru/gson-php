<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
declare(strict_types=1);

namespace Tebru\Gson\Context;

/**
 * Class WriterContext
 *
 * Runtime context that can be used during reading
 *
 * @author Nate Brunette <n@tebru.net>
 */
class WriterContext extends Context
{
    /**
     * If nulls should be serialized
     *
     * @var bool
     */
    private $serializeNull = false;

    /**
     * If we should serialize null
     *
     * @return bool
     */
    public function serializeNull(): bool
    {
        return $this->serializeNull;
    }

    /**
     * Set if nulls should be serialized
     *
     * @param bool $serializeNull
     * @return Context
     */
    public function setSerializeNull(bool $serializeNull): Context
    {
        $this->serializeNull = $serializeNull;

        return $this;
    }
}
