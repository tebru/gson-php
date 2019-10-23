<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\TypeAdapter;

use Tebru\Gson\Context\ReaderContext;
use Tebru\Gson\Context\WriterContext;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\TypeAdapter;
use Tebru\Gson\TypeAdapterFactory;
use Tebru\PhpType\TypeToken;

/**
 * Class StringTypeAdapter
 *
 * @author Nate Brunette <n@tebru.net>
 */
class StringTypeAdapter extends TypeAdapter implements TypeAdapterFactory
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
        return $type->phpType === TypeToken::STRING ? $this : null;
    }

    /**
     * Read the next value, convert it to its type and return it
     *
     * @param string|null $value
     * @param ReaderContext $context
     * @return string|null
     */
    public function read($value, ReaderContext $context): ?string
    {
        return $value === null ? null : (string)$value;
    }

    /**
     * Write the value to the writer for the type
     *
     * @param string|null $value
     * @param WriterContext $context
     * @return string|null
     */
    public function write($value, WriterContext $context): ?string
    {
        return $value === null ? null : (string)$value;
    }
}
