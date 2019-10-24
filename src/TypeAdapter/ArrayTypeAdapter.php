<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\TypeAdapter;

use LogicException;
use Tebru\Gson\Context\ReaderContext;
use Tebru\Gson\Context\WriterContext;
use Tebru\Gson\Exception\JsonSyntaxException;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\TypeAdapter;
use Tebru\PhpType\TypeToken;

/**
 * Class ArrayTypeAdapter
 *
 * @author Nate Brunette <n@tebru.net>
 */
class ArrayTypeAdapter extends TypeAdapter
{
    /**
     * @var TypeAdapterProvider
     */
    protected $typeAdapterProvider;

    /**
     * @var TypeToken
     */
    protected $keyType;

    /**
     * @var TypeAdapter
     */
    protected $valueTypeAdapter;

    /**
     * @var int
     */
    protected $numberOfGenerics;

    /**
     * A TypeAdapter cache keyed by raw type
     *
     * @var TypeAdapter[]
     */
    protected $adapters = [];

    /**
     * Constructor
     *
     * @param TypeAdapterProvider $typeAdapterProvider
     * @param TypeToken $keyType
     * @param TypeAdapter $valueTypeAdapter
     * @param int $numberOfGenerics
     */
    public function __construct(
        TypeAdapterProvider $typeAdapterProvider,
        TypeToken $keyType,
        TypeAdapter $valueTypeAdapter,
        int $numberOfGenerics
    ) {
        $this->typeAdapterProvider = $typeAdapterProvider;
        $this->keyType = $keyType;
        $this->valueTypeAdapter = $valueTypeAdapter;
        $this->numberOfGenerics = $numberOfGenerics;
    }

    /**
     * Read the next value, convert it to its type and return it
     *
     * @param array|null $value
     * @param ReaderContext $context
     * @return array|null
     */
    public function read($value, ReaderContext $context): ?array
    {
        if ($value === null) {
            return null;
        }

        if (!is_array($value)) {
            throw new JsonSyntaxException(sprintf('Could not parse json, expected array or object but found "%s"', gettype($value)));
        }

        $result = [];

        if ($this->numberOfGenerics > 2) {
            throw new LogicException('Array may not have more than 2 generic types');
        }

        if ($this->keyType->phpType !== TypeToken::WILDCARD
            && $this->keyType->phpType !== TypeToken::STRING
            && $this->keyType->phpType !== TypeToken::INTEGER
        ) {
            throw new LogicException('Array keys must be strings or integers');
        }

        $arrayIsObject = $this->numberOfGenerics === 2 || is_string(key($value));
        $enableScalarAdapters = $context->enableScalarAdapters();

        foreach ($value as $key => $item) {
            $itemValue = null;
            switch ($this->numberOfGenerics) {
                case 0:
                    if (!$enableScalarAdapters && ($item === null || is_scalar($item))) {
                        $itemValue = $item;
                        break;
                    }

                    if (!$arrayIsObject) {
                        $itemValue = $this->valueTypeAdapter->read($item, $context);
                        break;
                    }

                    if (is_array($item)) {
                        $itemValue = $this->read($item, $context);
                        break;
                    }

                    $type = TypeToken::createFromVariable($item);
                    $adapter = $this->adapters[$type->rawType] ?? $this->adapters[$type->rawType] = $this->typeAdapterProvider->getAdapter($type);
                    $itemValue = $adapter->read($item, $context);
                    break;
                case 1:
                    $itemValue = $this->valueTypeAdapter->read($item, $context);
                    break;
                case 2:
                    if (($this->keyType->phpType === TypeToken::INTEGER) && !ctype_digit((string)$key)) {
                        throw new JsonSyntaxException('Expected integer, but found string for key');
                    }

                    $itemValue = (!$enableScalarAdapters && ($item ===  null || is_scalar($item)))
                        ? $item
                        : $this->valueTypeAdapter->read($item, $context);
                    break;
            }

            $result[$arrayIsObject ? (string)$key : (int)$key] = $itemValue;
        }

        return $result;
    }

    /**
     * Write the value to the writer for the type
     *
     * @param array|null $value
     * @param WriterContext $context
     * @return array|null
     */
    public function write($value, WriterContext $context): ?array
    {
        if ($value === null) {
            return null;
        }

        if ($this->numberOfGenerics > 2) {
            throw new LogicException('Array may not have more than 2 generic types');
        }

        $arrayIsObject = $this->numberOfGenerics === 2 || is_string(key($value));
        $enableScalarAdapters = $context->enableScalarAdapters();
        $serializeNull = $context->serializeNull();
        $result = [];

        foreach ($value as $key => $item) {
            if ($item === null && !$serializeNull) {
                continue;
            }

            if (!$enableScalarAdapters && is_scalar($item)) {
                $result[$arrayIsObject ? (string)$key : (int)$key] = $item;
                continue;
            }

            $itemValue = null;
            switch ($this->numberOfGenerics) {
                // no generics specified
                case 0:
                    if (is_array($item)) {
                        $itemValue = $this->write($item, $context);
                        break;
                    }

                    $type = TypeToken::createFromVariable($item);
                    $adapter = $this->adapters[$type->rawType] ?? $this->adapters[$type->rawType] = $this->typeAdapterProvider->getAdapter($type);
                    $itemValue = $adapter->write($item, $context);
                    break;
                // generic for value specified
                case 1:
                case 2:
                    $itemValue = $this->valueTypeAdapter->write($item, $context);
                    break;
            }

            if ($itemValue === null && !$serializeNull) {
                continue;
            }

            $result[$arrayIsObject ? (string)$key : (int)$key] = $itemValue;
        }

        return $result;
    }
}
