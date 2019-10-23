<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\TypeAdapter\Factory;

use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\TypeAdapter;
use Tebru\Gson\TypeAdapterFactory;
use Tebru\PhpType\TypeToken;

/**
 * Class WrappedInterfaceTypeAdapterFactory
 *
 * @author Nate Brunette <n@tebru.net>
 */
class WrappedTypeAdapterFactory implements TypeAdapterFactory
{
    /**
     * @var TypeAdapter
     */
    protected $typeAdapter;

    /**
     * @var TypeToken
     */
    protected $type;
    /**
     * @var bool
     */
    protected $strict;

    /**
     * Constructor
     *
     * @param TypeAdapter $typeAdapter
     * @param TypeToken $type
     * @param bool $strict
     */
    public function __construct(TypeAdapter $typeAdapter, TypeToken $type, bool $strict)
    {
        $this->typeAdapter = $typeAdapter;
        $this->type = $type;
        $this->strict = $strict;
    }

    /**
     * Accepts the current type and a [@see TypeAdapterProvider] in case another type adapter needs
     * to be fetched during creation.  Should return a new instance of the TypeAdapter. Will return
     * null if the type adapter is not supported for the provided type.
     *
     * @param TypeToken $type
     * @param TypeAdapterProvider $typeAdapterProvider
     * @return TypeAdapter|null
     */
    public function create(TypeToken $type, TypeAdapterProvider $typeAdapterProvider): ?TypeAdapter
    {
        if ($this->strict) {
            return $type->rawType === $this->type->rawType ? $this->typeAdapter : null;
        }

        return $type->isA($this->type->rawType) ? $this->typeAdapter : null;
    }
}
