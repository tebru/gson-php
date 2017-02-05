<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal\TypeAdapter\Factory;

use Tebru\Collection\AbstractCollection;
use Tebru\Collection\AbstractList;
use Tebru\Collection\ArrayList;
use Tebru\Collection\CollectionInterface;
use Tebru\Collection\ListInterface;
use Tebru\Gson\Internal\PhpType;
use Tebru\Gson\Internal\TypeAdapter\ArrayListTypeAdapter;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\TypeAdapter;
use Tebru\Gson\TypeAdapterFactory;

/**
 * Class ArrayListTypeAdapterFactory
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class ArrayListTypeAdapterFactory implements TypeAdapterFactory
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

        return 'List' === $class
            || ArrayList::class === $class
            || ListInterface::class === $class
            || CollectionInterface::class === $class
            || AbstractCollection::class === $class
            || AbstractList::class === $class;
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
        return new ArrayListTypeAdapter($type, $typeAdapterProvider);
    }
}
