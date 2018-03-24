<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal\TypeAdapter;

use Tebru\Gson\Exception\JsonSyntaxException;
use Tebru\Gson\JsonWritable;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\JsonReadable;
use Tebru\Gson\JsonToken;
use Tebru\Gson\TypeAdapter;
use Tebru\PhpType\TypeToken;

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
     * @throws \Tebru\Gson\Exception\JsonSyntaxException If the token can't be processed
     */
    public function read(JsonReadable $reader)
    {
        switch ($reader->peek()) {
            case JsonToken::BEGIN_ARRAY:
                $type = new TypeToken(TypeToken::HASH);
                break;
            case JsonToken::BEGIN_OBJECT:
                $type = new TypeToken(TypeToken::OBJECT);
                break;
            case JsonToken::STRING:
                $type = new TypeToken(TypeToken::STRING);
                break;
            case JsonToken::NAME:
                $type = new TypeToken(TypeToken::STRING);
                break;
            case JsonToken::BOOLEAN:
                $type = new TypeToken(TypeToken::BOOLEAN);
                break;
            case JsonToken::NUMBER:
                $type = new TypeToken(TypeToken::FLOAT);
                break;
            case JsonToken::NULL:
                $type = new TypeToken(TypeToken::NULL);
                break;
            default:
                throw new JsonSyntaxException(
                    \sprintf(
                        'Could not parse token "%s" at "%s"',
                        $reader->peek(),
                        $reader->getPath()
                    )
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
        $adapter = $this->typeAdapterProvider->getAdapter(TypeToken::createFromVariable($value));
        $adapter->write($writer, $value);
    }
}
