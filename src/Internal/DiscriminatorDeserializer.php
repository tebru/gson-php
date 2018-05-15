<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal;

use Tebru\Gson\Discriminator;
use Tebru\Gson\Element\JsonElement;
use Tebru\Gson\JsonDeserializationContext;
use Tebru\Gson\JsonDeserializer;
use Tebru\PhpType\TypeToken;

/**
 * Class DiscriminatorDeserializer
 *
 * Uses a [@see Discriminator] to provide the target class and delegates deserialization.
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class DiscriminatorDeserializer implements JsonDeserializer
{
    /**
     * @var Discriminator
     */
    private $discriminator;

    /**
     * Constructor
     *
     * @param Discriminator $discriminator
     */
    public function __construct(Discriminator $discriminator)
    {
        $this->discriminator = $discriminator;
    }

    /**
     * Called during deserialization process, passing in the JsonElement for the type.  Use
     * the JsonDeserializationContext if you want to delegate deserialization of sub types.
     *
     * @param JsonElement $jsonElement
     * @param TypeToken $type
     * @param JsonDeserializationContext $context
     * @return mixed
     */
    public function deserialize(JsonElement $jsonElement, TypeToken $type, JsonDeserializationContext $context)
    {
        return $context->deserialize($jsonElement, $this->discriminator->getClass($jsonElement->asJsonObject()));
    }
}
