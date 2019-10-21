<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson;

/**
 * Interface JsonDeserializationContext
 *
 * An instance of this interface will be passed to a custom deserializer.  Use this
 * instance to delegate deserialization.
 *
 * @author Nate Brunette <n@tebru.net>
 */
interface JsonDeserializationContext
{
    /**
     * Delegate deserialization of normalized data.  Should not be called on the original
     * element as doing so will result in an infinite loop.  Should return a deserialized
     * object.
     *
     * @param mixed $value
     * @param string $type
     * @return mixed
     */
    public function deserialize($value, string $type);
}
