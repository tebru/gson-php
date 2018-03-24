<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal;

use Tebru\Gson\Element\JsonElement;
use Tebru\Gson\JsonDeserializationContext;
use Tebru\PhpType\TypeToken;

/**
 * Class DefaultJsonDeserializationContext
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class DefaultJsonDeserializationContext implements JsonDeserializationContext
{
    /**
     * @var TypeAdapterProvider
     */
    private $typeAdapterProvider;

    /**
     * Constructor
     *
     * @param TypeAdapterProvider $typeAdapterProvider
     */
    public function __construct(TypeAdapterProvider $typeAdapterProvider)
    {
        $this->typeAdapterProvider = $typeAdapterProvider;
    }

    /**
     * Delegate deserialization of a JsonElement.  Should not be called on the original
     * element as doing so will result in an infinite loop.  Should return a deserialized
     * object.
     *
     * @param JsonElement $jsonElement
     * @param string $type
     * @return mixed
     */
    public function deserialize(JsonElement $jsonElement, string $type)
    {
        $typeAdapter = $this->typeAdapterProvider->getAdapter(new TypeToken($type));

        return $typeAdapter->readFromJsonElement($jsonElement);
    }
}
