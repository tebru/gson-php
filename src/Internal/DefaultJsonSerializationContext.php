<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal;

use Tebru\Gson\Element\JsonElement;
use Tebru\Gson\JsonSerializationContext;
use Tebru\PhpType\TypeToken;

/**
 * Class DefaultJsonSerializationContext
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class DefaultJsonSerializationContext implements JsonSerializationContext
{
    /**
     * @var TypeAdapterProvider
     */
    private $typeAdapterProvider;

    /**
     * @var bool
     */
    private $serializeNull;

    /**
     * Constructor
     *
     * @param TypeAdapterProvider $typeAdapterProvider
     * @param bool $serializeNull
     */
    public function __construct(TypeAdapterProvider $typeAdapterProvider, bool $serializeNull)
    {
        $this->typeAdapterProvider = $typeAdapterProvider;
        $this->serializeNull = $serializeNull;
    }

    /**
     * Delegate serialization of an object.  Should never be called with the original object
     * as doing so will result in an infinite loop.  Will return a JsonElement.
     *
     * @param mixed $object
     * @return JsonElement
     */
    public function serialize($object): JsonElement
    {
        $typeAdapter = $this->typeAdapterProvider->getAdapter(TypeToken::createFromVariable($object));

        return $typeAdapter->writeToJsonElement($object, $this->serializeNull);
    }
}
