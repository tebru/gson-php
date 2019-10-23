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
use Tebru\PhpType\TypeToken;

/**
 * Class WildcardTypeAdapter
 *
 * @author Nate Brunette <n@tebru.net>
 */
class WildcardTypeAdapter extends TypeAdapter
{
    /**
     * @var TypeAdapterProvider
     */
    protected $typeAdapterProvider;

    /**
     * Constructor
     *
     * @param TypeAdapterProvider $typeAdapterProvider
     */
    public function __construct(TypeAdapterProvider $typeAdapterProvider)
    {
        $this->typeAdapterProvider = $typeAdapterProvider;
    }

    /**
     * Read the next value, convert it to its type and return it
     *
     * @param $value
     * @param ReaderContext $context
     * @return mixed
     */
    public function read($value, ReaderContext $context)
    {
        $type = TypeToken::createFromVariable($value);
        if ($type->genericTypes === [] && !$context->enableScalarAdapters() && $type->isScalar()) {
            return $value;
        }

        return $this->typeAdapterProvider->getAdapter($type)->read($value, $context);
    }

    /**
     * Write the value to the writer for the type
     *
     * @param mixed $value
     * @param WriterContext $context
     * @return mixed
     */
    public function write($value, WriterContext $context)
    {
        $type = TypeToken::createFromVariable($value);
        if ($type->genericTypes === [] && !$context->enableScalarAdapters() && $type->isScalar()) {
            return $value;
        }

        return $this->typeAdapterProvider->getAdapter($type)->write($value, $context);
    }
}
