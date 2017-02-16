<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal\TypeAdapter\Factory;

use Tebru\Collection\AbstractMap;
use Tebru\Collection\HashMap;
use Tebru\Collection\MapInterface;
use Tebru\Gson\PhpType;
use Tebru\Gson\Internal\TypeAdapter\HashMapTypeAdapter;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\TypeAdapter;
use Tebru\Gson\TypeAdapterFactory;

/**
 * Class HashMapTypeAdapterFactory
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class HashMapTypeAdapterFactory implements TypeAdapterFactory
{
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

        $class = $type->getClass();

        return 'Map' === $class
            || HashMap::class === $class
            || MapInterface::class === $class
            || AbstractMap::class === $class;
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
        return new HashMapTypeAdapter($type, $typeAdapterProvider);
    }
}
