<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal;

use Tebru\Gson\Context\ReaderContext;
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
     * @var ReaderContext
     */
    private $context;

    /**
     * Constructor
     *
     * @param TypeAdapterProvider $typeAdapterProvider
     * @param ReaderContext $context
     */
    public function __construct(TypeAdapterProvider $typeAdapterProvider, ReaderContext $context)
    {
        $this->typeAdapterProvider = $typeAdapterProvider;
        $this->context = $context;
    }

    /**
     * Delegate deserialization of normalized data.  Should not be called on the original
     * element as doing so will result in an infinite loop.  Should return a deserialized
     * object.
     *
     * @param mixed $value
     * @param string $type
     * @return mixed
     */
    public function deserialize($value, string $type)
    {
        $typeAdapter = $this->typeAdapterProvider->getAdapter(TypeToken::create($type));

        return $typeAdapter->read($value, $this->context);
    }
}
