<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\TypeAdapter\Factory;

use stdClass;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\TypeAdapter;
use Tebru\Gson\TypeAdapter\ArrayTypeAdapter;
use Tebru\Gson\TypeAdapter\WildcardTypeAdapter;
use Tebru\Gson\TypeAdapterFactory;
use Tebru\PhpType\TypeToken;

/**
 * Class ArrayTypeAdapterFactory
 *
 * @author Nate Brunette <n@tebru.net>
 */
class ArrayTypeAdapterFactory implements TypeAdapterFactory
{
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
        if (!$type->isArray() && !$type->isA(stdClass::class)) {
            return null;
        }

        $genericTypes = $type->getGenerics();
        $numberOfGenericTypes = \count($genericTypes);
        $keyType = TypeToken::create(TypeToken::WILDCARD);

        switch ($numberOfGenericTypes) {
            case 1:
                $valueTypeAdapter = $typeAdapterProvider->getAdapter($genericTypes[0]);
                break;
            case 2:
                $keyType = $genericTypes[0];
                $valueTypeAdapter = $typeAdapterProvider->getAdapter($genericTypes[1]);
                break;
            default:
                $valueTypeAdapter = new WildcardTypeAdapter($typeAdapterProvider);
        }

        return new ArrayTypeAdapter($typeAdapterProvider, $keyType, $valueTypeAdapter, $numberOfGenericTypes);
    }
}
