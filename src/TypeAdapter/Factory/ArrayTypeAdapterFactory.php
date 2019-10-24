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
use Tebru\Gson\TypeAdapter\ScalarArrayTypeAdapter;
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
     * @var bool
     */
    protected $enableScalarAdapters;

    /**
     * Constructor
     *
     * @param bool $enableScalarAdapters
     */
    public function __construct(bool $enableScalarAdapters)
    {
        $this->enableScalarAdapters = $enableScalarAdapters;
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
        if ($type->phpType !== TypeToken::HASH && !$type->isA(stdClass::class)) {
            return null;
        }

        $genericTypes = $type->genericTypes;
        $numberOfGenericTypes = count($genericTypes);

        $keyType = TypeToken::create(TypeToken::WILDCARD);
        $valueType = TypeToken::create(TypeToken::WILDCARD);

        if ($numberOfGenericTypes === 1) {
            $valueType = $genericTypes[0];
        } elseif ($numberOfGenericTypes === 2) {
            [$keyType, $valueType] = $genericTypes;
        }

        if (!$this->enableScalarAdapters && $valueType->isScalar()) {
            if ($numberOfGenericTypes < 2 && $valueType->genericTypes === []) {
                return new ScalarArrayTypeAdapter();
            }

            $valueTypeAdapter = new TypeAdapter\WildcardTypeAdapter($typeAdapterProvider);
        } else {
            $valueTypeAdapter = $typeAdapterProvider->getAdapter($valueType);
        }


        return new ArrayTypeAdapter($typeAdapterProvider, $keyType, $valueTypeAdapter, $numberOfGenericTypes);
    }
}
