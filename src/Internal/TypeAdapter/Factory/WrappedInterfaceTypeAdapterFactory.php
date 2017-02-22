<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal\TypeAdapter\Factory;

use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\PhpType;
use Tebru\Gson\TypeAdapter;
use Tebru\Gson\TypeAdapterFactory;

/**
 * Class WrappedInterfaceTypeAdapterFactory
 *
 * @author Nate Brunette <n@tebru.net>
 */
class WrappedInterfaceTypeAdapterFactory implements TypeAdapterFactory
{
    /**
     * @var TypeAdapter
     */
    private $typeAdapter;

    /**
     * @var string
     */
    private $interfaceName;

    /**
     * Constructor
     *
     * @param TypeAdapter $typeAdapter
     * @param string $interfaceName
     */
    public function __construct(TypeAdapter $typeAdapter, string $interfaceName)
    {
        $this->typeAdapter = $typeAdapter;
        $this->interfaceName = $interfaceName;
    }

    /**
     * Will be called before ::create() is called.  The current type will be passed
     * in.  Return false if ::create() should not be called.
     *
     * @param PhpType $type
     * @return bool
     */
    public function supports(PhpType $type): bool
    {
        if (!$type->isObject()) {
            return false;
        }

        return $type->isA($this->interfaceName);
    }

    /**
     * Accepts the current type and a [@see TypeAdapterProvider] in case another type adapter needs
     * to be fetched during creation.  Should return a new instance of the TypeAdapter.
     *
     * @param PhpType $type
     * @param TypeAdapterProvider $typeAdapterProvider
     * @return TypeAdapter
     */
    public function create(PhpType $type, TypeAdapterProvider $typeAdapterProvider): TypeAdapter
    {
        return $this->typeAdapter;
    }
}
