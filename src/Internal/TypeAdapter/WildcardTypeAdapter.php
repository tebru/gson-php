<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal\TypeAdapter;

use Tebru\Gson\Exception\UnexpectedJsonTokenException;
use Tebru\Gson\Internal\JsonWritable;
use Tebru\Gson\Internal\PhpType;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\Internal\TypeToken;
use Tebru\Gson\JsonReadable;
use Tebru\Gson\JsonToken;
use Tebru\Gson\TypeAdapter;

/**
 * Class WildcardTypeAdapter
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class WildcardTypeAdapter extends TypeAdapter
{
    /**
     * @var TypeAdapterProvider
     */
    private $typeAdapterProvider;

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
     * @param JsonReadable $reader
     * @return mixed
     * @throws \Tebru\Gson\Exception\UnexpectedJsonTokenException If the token can't be processed
     * @throws \Tebru\Gson\Exception\MalformedTypeException If the type cannot be parsed
     * @throws \InvalidArgumentException if the type cannot be handled by a type adapter
     */
    public function read(JsonReadable $reader)
    {
        switch ($reader->peek()) {
            case JsonToken::BEGIN_ARRAY:
                $type = new PhpType(TypeToken::ARRAY);
                break;
            case JsonToken::BEGIN_OBJECT:
                $type = new PhpType(TypeToken::OBJECT);
                break;
            case JsonToken::STRING:
                $type = new PhpType(TypeToken::STRING);
                break;
            case JsonToken::NAME:
                $type = new PhpType(TypeToken::STRING);
                break;
            case JsonToken::BOOLEAN:
                $type = new PhpType(TypeToken::BOOLEAN);
                break;
            case JsonToken::NUMBER:
                $type = new PhpType(TypeToken::FLOAT);
                break;
            case JsonToken::NULL:
                $type = new PhpType(TypeToken::NULL);
                break;
            default:
                throw new UnexpectedJsonTokenException(
                    sprintf('Could not parse token "%s"', $reader->peek())
                );
        }

        return $this->typeAdapterProvider->getAdapter($type)->read($reader);
    }

    /**
     * Write the value to the writer for the type
     *
     * @param JsonWritable $writer
     * @param mixed $value
     * @return void
     */
    public function write(JsonWritable $writer, $value): void
    {
    }
}
