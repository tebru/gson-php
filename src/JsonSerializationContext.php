<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson;

/**
 * Interface JsonSerializationContext
 *
 * Serialization context that is passed to a custom serializer.  Use this
 * context to delegate serialization.
 *
 * @author Nate Brunette <n@tebru.net>
 */
interface JsonSerializationContext
{
    /**
     * Delegate serialization of an object.  Should never be called with the original object
     * as doing so will result in an infinite loop.  Will return normalized data.
     *
     * @param mixed $object
     * @return mixed
     */
    public function serialize($object);
}
