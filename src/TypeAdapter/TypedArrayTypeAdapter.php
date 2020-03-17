<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\TypeAdapter;

use Tebru\Gson\Context\ReaderContext;
use Tebru\Gson\Context\WriterContext;
use Tebru\Gson\Exception\JsonSyntaxException;
use Tebru\Gson\TypeAdapter;

/**
 * Class TypedArrayTypeAdapter
 *
 * @author Nate Brunette <n@tebru.net>
 */
class TypedArrayTypeAdapter extends TypeAdapter
{
    /**
     * @var TypeAdapter
     */
    protected $valueTypeAdapter;

    /**
     * @var bool
     */
    private $stringKeys;

    /**
     * Constructor
     *
     * @param TypeAdapter $valueTypeAdapter
     * @param bool $stringKeys
     */
    public function __construct(TypeAdapter $valueTypeAdapter, bool $stringKeys)
    {
        $this->valueTypeAdapter = $valueTypeAdapter;
        $this->stringKeys = $stringKeys;
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
        $arrayIsObject = is_string(key($value));

        if ($arrayIsObject !== $this->stringKeys) {
            throw new JsonSyntaxException(
                sprintf(
                    'Expected %s, but found %s for key',
                    $this->stringKeys ? 'string' : 'integer',
                    $arrayIsObject ? 'string' : 'integer'
                )
            );
        }

        foreach ($value as $key => $item) {
            $itemValue = $this->valueTypeAdapter->read($item, $context);
            if ($this->stringKeys) {
                $result[(string)$key] = $itemValue;
            } else {
                $result[] = $itemValue;
            }
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

        $serializeNull = $context->serializeNull();
        $result = [];
        $arrayIsObject = is_string(key($value));

        if ($arrayIsObject !== $this->stringKeys) {
            throw new JsonSyntaxException(
                sprintf(
                    'Expected %s, but found %s for key',
                    $this->stringKeys ? 'string' : 'integer',
                    $arrayIsObject ? 'string' : 'integer'
                )
            );
        }

        foreach ($value as $key => $item) {
            if ($item === null && !$serializeNull) {
                continue;
            }

            $itemValue = $this->valueTypeAdapter->write($item, $context);
            if ($itemValue === null && !$serializeNull) {
                continue;
            }

            if ($this->stringKeys) {
                $result[(string)$key] = $itemValue;
            } else {
                $result[] = $itemValue;
            }
        }

        return $result;
    }

    /**
     * Return true if object can be written to disk
     *
     * @return bool
     */
    public function canCache(): bool
    {
        return $this->valueTypeAdapter->canCache();
    }
}
