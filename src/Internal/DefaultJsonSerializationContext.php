<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal;

use Tebru\Gson\Context\WriterContext;
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
     * @var WriterContext
     */
    private $context;

    /**
     * Constructor
     *
     * @param TypeAdapterProvider $typeAdapterProvider
     * @param WriterContext $context
     */
    public function __construct(TypeAdapterProvider $typeAdapterProvider, WriterContext $context)
    {
        $this->typeAdapterProvider = $typeAdapterProvider;
        $this->context = $context;
    }

    /**
     * Delegate serialization of an object. Should never be called with the original object
     * as doing so will result in an infinite loop. Should return normalized data.
     *
     * @param mixed $object
     * @return mixed
     */
    public function serialize($object)
    {
        $typeAdapter = $this->typeAdapterProvider->getAdapter(TypeToken::createFromVariable($object));

        return $typeAdapter->write($object, $this->context);
    }
}
