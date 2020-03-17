<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\TypeAdapter\Factory;

use InvalidArgumentException;
use LogicException;
use stdClass;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\TypeAdapter;
use Tebru\Gson\TypeAdapter\ArrayTypeAdapter;
use Tebru\Gson\TypeAdapter\ScalarArrayTypeAdapter;
use Tebru\Gson\TypeAdapter\TypedArrayTypeAdapter;
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

        if ($numberOfGenericTypes > 2) {
            throw new LogicException('Array may not have more than 2 generic types');
        }

        $keyType = $valueType = TypeToken::create(TypeToken::WILDCARD);

        if ($numberOfGenericTypes === 1) {
            $keyType = TypeToken::create(TypeToken::INTEGER);
            $valueType = $genericTypes[0];
        } elseif ($numberOfGenericTypes === 2) {
            [$keyType, $valueType] = $genericTypes;
        }

        if ($keyType->phpType !== TypeToken::WILDCARD && $keyType->phpType !== TypeToken::INTEGER && $keyType->phpType !== TypeToken::STRING) {
            throw new LogicException('Array keys must be strings or integers');
        }

        if (!$this->enableScalarAdapters && $numberOfGenericTypes <= 2 && $valueType->genericTypes === [] && $valueType->isScalar()) {
            return new ScalarArrayTypeAdapter();
        }

        $valueTypeAdapter = $typeAdapterProvider->getAdapter($valueType);

        if (!$valueTypeAdapter instanceof WildcardTypeAdapter && !$keyType->phpType !== TypeToken::WILDCARD && $numberOfGenericTypes >= 1) {
            return new TypedArrayTypeAdapter($valueTypeAdapter, $keyType->phpType === TypeToken::STRING);
        }

        return new ArrayTypeAdapter();
    }

    /**
     * Return true if object can be written to disk
     *
     * @return bool
     */
    public function canCache(): bool
    {
        return false;
    }
}
