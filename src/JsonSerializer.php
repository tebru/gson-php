<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson;

use Tebru\PhpType\TypeToken;

/**
 * Interface JsonSerializer
 *
 * Defines a custom serializer for a specific type.
 *
 * @author Nate Brunette <n@tebru.net>
 */
interface JsonSerializer
{
    /**
     * Called during serialization process, passing in the object and type that should
     * be serialized. Delegate serialization using the provided context.
     *
     * @param mixed $object
     * @param TypeToken $type
     * @param JsonSerializationContext $context
     * @return mixed
     */
    public function serialize($object, TypeToken $type, JsonSerializationContext $context);
}
