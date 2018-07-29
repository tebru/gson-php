<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal\TypeAdapter;

use LogicException;
use Tebru\Gson\Exception\JsonSyntaxException;
use Tebru\Gson\JsonWritable;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\JsonReadable;
use Tebru\Gson\JsonToken;
use Tebru\Gson\TypeAdapter;
use Tebru\PhpType\TypeToken;

/**
 * Class ArrayTypeAdapter
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class ArrayTypeAdapter extends TypeAdapter
{
    /**
     * @var TypeToken
     */
    private $type;

    /**
     * @var string
     */
    private $typeString;

    /**
     * @var TypeAdapterProvider
     */
    private $typeAdapterProvider;

    /**
     * An array of cached type adapters
     *
     * @var TypeAdapter[]
     */
    private $adapters = [];

    /**
     * Constructor
     *
     * @param TypeToken $type
     * @param TypeAdapterProvider $typeAdapterProvider
     */
    public function __construct(TypeToken $type, TypeAdapterProvider $typeAdapterProvider)
    {
        $this->type = $type;
        $this->typeString = (string)$type;
        $this->typeAdapterProvider = $typeAdapterProvider;
    }

    /**
     * Read the next value, convert it to its type and return it
     *
     * @param JsonReadable $reader
     * @return array|null
     * @throws \LogicException
     * @throws \Tebru\Gson\Exception\JsonSyntaxException If trying to read from non object/array
     */
    public function read(JsonReadable $reader): ?array
    {
        $token = $reader->peek();
        if ($token === JsonToken::NULL) {
            $reader->nextNull();
            return null;
        }

        $array = [];
        $generics = $this->type->getGenerics();
        $numberOfGenerics = \count($generics);

        if ($numberOfGenerics > 2) {
            throw new LogicException(\sprintf('Array may not have more than 2 generic types at "%s"', $reader->getPath()));
        }

        $adapter = $this->adapters[$this->typeString] ?? null;
        if ($adapter === null) {
            switch ($numberOfGenerics) {
                case 1:
                    $adapter = $this->typeAdapterProvider->getAdapter($generics[0]);
                    $this->adapters[$this->typeString] = $adapter;
                    break;
                case 2:
                    $adapter = $this->typeAdapterProvider->getAdapter($generics[1]);
                    $this->adapters[$this->typeString] = $adapter;
                    break;
            }
        }

        switch ($token) {
            case JsonToken::BEGIN_OBJECT:
                $reader->beginObject();

                while ($reader->hasNext()) {
                    $name = $reader->nextName();

                    switch ($numberOfGenerics) {
                        // no generics specified
                        case 0:
                            // By now we know that we're deserializing a json object to an array.
                            // If there is a nested object, continue deserializing to an array,
                            // otherwise guess the type using the wildcard
                            $type = $reader->peek() === JsonToken::BEGIN_OBJECT
                                ? TypeToken::create(TypeToken::HASH)
                                : TypeToken::create(TypeToken::WILDCARD);

                            $adapter = $this->typeAdapterProvider->getAdapter($type);
                            $array[$name] = $adapter->read($reader);

                            break;
                        // generic for value specified
                        case 1:
                            $array[$name] = $adapter->read($reader);

                            break;
                        // generic for key and value specified
                        case 2:
                            /** @var TypeToken $keyType */
                            $keyType = $generics[0];

                            if (!$keyType->isString() && !$keyType->isInteger()) {
                                throw new LogicException(\sprintf('Array keys must be strings or integers at "%s"', $reader->getPath()));
                            }

                            if ($keyType->isInteger()) {
                                if (!\ctype_digit($name)) {
                                    throw new JsonSyntaxException(\sprintf('Expected integer, but found string for key at "%s"', $reader->getPath()));
                                }

                                $name = (int)$name;
                            }

                            $array[$name] = $adapter->read($reader);

                            break;
                    }
                }

                $reader->endObject();

                break;
            case JsonToken::BEGIN_ARRAY:
                $reader->beginArray();

                while ($reader->hasNext()) {
                    switch ($numberOfGenerics) {
                        // no generics specified
                        case 0:
                            $adapter = $this->typeAdapterProvider->getAdapter(TypeToken::create(TypeToken::WILDCARD));
                            $array[] = $adapter->read($reader);

                            break;
                        case 1:
                            $array[] = $adapter->read($reader);

                            break;
                        default:
                            throw new LogicException(\sprintf('An array may only specify a generic type for the value at "%s"', $reader->getPath()));
                    }
                }

                $reader->endArray();

                break;
            default:
                throw new JsonSyntaxException(\sprintf('Could not parse json, expected array or object but found "%s" at "%s"', $token, $reader->getPath()));
        }

        return $array;
    }

    /**
     * Write the value to the writer for the type
     *
     * @param JsonWritable $writer
     * @param array $value
     * @return void
     * @throws \LogicException
     */
    public function write(JsonWritable $writer, $value): void
    {
        if (null === $value) {
            $writer->writeNull();

            return;
        }

        $generics = $this->type->getGenerics();
        $numberOfGenerics = \count($generics);
        if ($numberOfGenerics > 2) {
            throw new LogicException('Array may not have more than 2 generic types');
        }

        $arrayIsObject = $this->isArrayObject($value, $numberOfGenerics);

        if ($arrayIsObject) {
            $writer->beginObject();
        } else {
            $writer->beginArray();
        }

        $adapter = $this->adapters[$this->typeString] ?? null;
        if ($adapter === null) {
            switch ($numberOfGenerics) {
                case 1:
                    $adapter = $this->typeAdapterProvider->getAdapter($generics[0]);
                    $this->adapters[$this->typeString] = $adapter;
                    break;
                case 2:
                    $adapter = $this->typeAdapterProvider->getAdapter($generics[1]);
                    $this->adapters[$this->typeString] = $adapter;
                    break;
            }
        }

        foreach ($value as $key => $item) {
            switch ($numberOfGenerics) {
                // no generics specified
                case 0:
                    if ($arrayIsObject) {
                        $writer->name((string)$key);
                    }

                    $adapter = $this->typeAdapterProvider->getAdapter(TypeToken::createFromVariable($item));
                    $adapter->write($writer, $item);

                    break;
                // generic for value specified
                case 1:
                    if ($arrayIsObject) {
                        $writer->name((string)$key);
                    }

                    $adapter->write($writer, $item);

                    break;
                // generic for key and value specified
                case 2:
                    $writer->name($key);
                    $adapter->write($writer, $item);

                    break;
            }
        }

        if ($arrayIsObject) {
            $writer->endObject();
        } else {
            $writer->endArray();
        }
    }

    /**
     * Returns true if the array is acting like an object
     * @param array $array
     * @param int $numberOfGenerics
     * @return bool
     */
    private function isArrayObject(array $array, int $numberOfGenerics): bool
    {
        if (2 === $numberOfGenerics) {
            return true;
        }

        return \is_string(\key($array));
    }
}
