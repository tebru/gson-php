<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal\TypeAdapter;

use LogicException;
use Tebru\Gson\Exception\UnexpectedJsonTokenException;
use Tebru\Gson\Internal\JsonDecodeReader;
use Tebru\Gson\Internal\JsonWritable;
use Tebru\Gson\Internal\PhpType;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\Internal\TypeToken;
use Tebru\Gson\JsonReadable;
use Tebru\Gson\JsonToken;
use Tebru\Gson\TypeAdapter;

/**
 * Class ArrayTypeAdapter
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class ArrayTypeAdapter extends TypeAdapter
{
    /**
     * @var PhpType
     */
    private $phpType;

    /**
     * @var TypeAdapterProvider
     */
    private $typeAdapterProvider;

    /**
     * Constructor
     *
     * @param PhpType $phpType
     * @param TypeAdapterProvider $typeAdapterProvider
     */
    public function __construct(PhpType $phpType, TypeAdapterProvider $typeAdapterProvider)
    {
        $this->phpType = $phpType;
        $this->typeAdapterProvider = $typeAdapterProvider;
    }

    /**
     * Read the next value, convert it to its type and return it
     *
     * @param JsonReadable $reader
     * @return mixed
     * @throws \Tebru\Gson\Exception\UnexpectedJsonTokenException If trying to read from non object/array
     * @throws \Tebru\Gson\Exception\MalformedTypeException If the type cannot be parsed
     * @throws \OutOfBoundsException if the index doesn't exist
     * @throws \InvalidArgumentException if the type cannot be handled by a type adapter
     * @throws \LogicException If the wrong number of generics exist
     * @throws \RuntimeException If the value is not valid
     */
    public function read(JsonReadable $reader): ?array
    {
        if ($reader->peek() === JsonToken::NULL) {
            return $reader->nextNull();
        }

        $array = [];
        $token = $reader->peek();
        $generics = $this->phpType->getGenerics();

        if (count($generics) > 2) {
            throw new LogicException('Array may not have more than 2 generic types');
        }

        switch ($token) {
            case JsonToken::BEGIN_OBJECT:
                $reader->beginObject();

                while ($reader->hasNext()) {
                    $name = $reader->nextName();

                    switch (count($generics)) {
                        // no generics specified
                        case 0:
                            // By now we know that we're deserializing a json object to an array.
                            // If there is a nested object, continue deserializing to an array,
                            // otherwise guess the type using the wildcard
                            $type = $reader->peek() === JsonToken::BEGIN_OBJECT
                                ? new PhpType(TypeToken::ARRAY)
                                : new PhpType(TypeToken::WILDCARD);

                            $adapter = $this->typeAdapterProvider->getAdapter($type);
                            $array[$name] = $adapter->read($reader);

                            break;
                        // generic for value specified
                        case 1:
                            $adapter = $this->typeAdapterProvider->getAdapter($generics[0]);
                            $array[$name] = $adapter->read($reader);

                            break;
                        // generic for key and value specified
                        case 2:
                            /** @var PhpType $keyType */
                            $keyType = $generics[0];
                            if ($keyType->isString()) {
                                $name = sprintf('"%s"', $name);
                            }

                            $keyAdapter = $this->typeAdapterProvider->getAdapter($keyType);
                            $name = $keyAdapter->read(new JsonDecodeReader($name));

                            $valueAdapter = $this->typeAdapterProvider->getAdapter($generics[1]);
                            $array[$name] = $valueAdapter->read($reader);

                            break;
                    }
                }

                $reader->endObject();

                break;
            case JsonToken::BEGIN_ARRAY:
                $reader->beginArray();

                while ($reader->hasNext()) {
                    switch (count($generics)) {
                        // no generics specified
                        case 0:
                            $adapter = $this->typeAdapterProvider->getAdapter(new PhpType(TypeToken::WILDCARD));
                            $array[] = $adapter->read($reader);

                            break;
                        case 1:
                            $adapter = $this->typeAdapterProvider->getAdapter($generics[0]);
                            $array[] = $adapter->read($reader);

                            break;
                        default:
                            throw new LogicException('An array may only specify a generic type for the value');
                    }
                }

                $reader->endArray();

                break;
            default:
                throw new UnexpectedJsonTokenException(sprintf('Could not parse json, expected array or object but found "%s"', $token));
        }

        return $array;
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
