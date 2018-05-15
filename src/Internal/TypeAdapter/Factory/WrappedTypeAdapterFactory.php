<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal\TypeAdapter\Factory;

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
    private $typeAdapter;

    /**
     * @var TypeToken
     */
    private $type;
    /**
     * @var bool
     */
    private $strict;

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
     * Will be called before ::create() is called.  The current type will be passed
     * in.  Return false if ::create() should not be called.
     *
     * @param TypeToken $type
     * @return bool
     */
    public function supports(TypeToken $type): bool
    {
        return $this->strict
            ? $type->getRawType() === $this->type->getRawType()
            : $type->isA($this->type->getRawType());
    }

    /**
     * Accepts the current type and a [@see TypeAdapterProvider] in case another type adapter needs
     * to be fetched during creation.  Should return a new instance of the TypeAdapter.
     *
     * @param TypeToken $type
     * @param TypeAdapterProvider $typeAdapterProvider
     * @return TypeAdapter
     */
    public function create(TypeToken $type, TypeAdapterProvider $typeAdapterProvider): TypeAdapter
    {
        return $this->typeAdapter;
    }
}
