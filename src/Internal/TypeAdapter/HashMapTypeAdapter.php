<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal\TypeAdapter;

use LogicException;
use Tebru\Collection\HashMap;
use Tebru\Collection\MapEntry;
use Tebru\Collection\MapInterface;
use Tebru\Gson\Internal\JsonDecodeReader;
use Tebru\Gson\JsonWritable;
use Tebru\Gson\PhpType;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\Internal\TypeToken;
use Tebru\Gson\JsonReadable;
use Tebru\Gson\JsonToken;
use Tebru\Gson\TypeAdapter;

/**
 * Class HashMapTypeAdapter
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class HashMapTypeAdapter extends TypeAdapter
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
     * @return HashMap|null
     * @throws \InvalidArgumentException if the type cannot be handled by a type adapter
     * @throws \LogicException If there is an incorrect number of generic types
     * @throws \Tebru\Gson\Exception\MalformedTypeException If the type cannot be parsed
     */
    public function read(JsonReadable $reader): ?HashMap
    {
        if ($reader->peek() === JsonToken::NULL) {
            return $reader->nextNull();
        }

        $generics = $this->phpType->getGenerics();
        $numberOfGenerics = count($generics);
        if ($numberOfGenerics > 2) {
            throw new LogicException('HashMap must have one or two generic types');
        }

        $hashMap = new HashMap();

        $reader->beginObject();
        while ($reader->hasNext()) {
            $name = $reader->nextName();

            switch ($numberOfGenerics) {
                case 0:
                    switch ($reader->peek()) {
                        case JsonToken::BEGIN_ARRAY:
                            $type = new PhpType('List');
                            break;
                        case JsonToken::BEGIN_OBJECT:
                            $type = new PhpType('Map');
                            break;
                        default:
                            $type = new PhpType(TypeToken::WILDCARD);
                    }

                    $adapter = $this->typeAdapterProvider->getAdapter($type);
                    $hashMap->put($name, $adapter->read($reader));

                    break;
                case 1:
                    $adapter = $this->typeAdapterProvider->getAdapter($generics[0]);
                    $hashMap->put($name, $adapter->read($reader));
                    break;
                case 2:
                    /** @var PhpType $keyType */
                    $keyType = $generics[0];
                    if ($keyType->isString()) {
                        $name = sprintf('"%s"', $name);
                    }

                    $adapter = $this->typeAdapterProvider->getAdapter($keyType);
                    $name = $adapter->read(new JsonDecodeReader($name));

                    $adapter = $this->typeAdapterProvider->getAdapter($generics[1]);
                    $hashMap->put($name, $adapter->read($reader));

                    break;
            }
        }
        $reader->endObject();

        return $hashMap;
    }

    /**
     * Write the value to the writer for the type
     *
     * @param JsonWritable $writer
     * @param MapInterface $value
     * @return void
     * @throws \InvalidArgumentException if the type cannot be handled by a type adapter
     * @throws \LogicException If there is an incorrect number of generic types
     * @throws \Tebru\Gson\Exception\MalformedTypeException If the type cannot be parsed
     */
    public function write(JsonWritable $writer, $value): void
    {
        if (null === $value) {
            $writer->writeNull();

            return;
        }

        $generics = $this->phpType->getGenerics();
        $numberOfGenerics = count($generics);
        if ($numberOfGenerics > 2) {
            throw new LogicException('HashMap must have one or two generic types');
        }

        $writer->beginObject();

        /** @var MapEntry $mapEntry */
        foreach ($value->entrySet() as $mapEntry) {
            switch ($numberOfGenerics) {
                case 0:
                    $writer->name($mapEntry->key);
                    $value = $mapEntry->value;
                    $adapter = $this->typeAdapterProvider->getAdapter(PhpType::createFromVariable($value));
                    $adapter->write($writer, $value);

                    break;
                case 1:
                    $writer->name($mapEntry->key);
                    $value = $mapEntry->value;
                    $adapter = $this->typeAdapterProvider->getAdapter($generics[0]);
                    $adapter->write($writer, $value);

                    break;
                case 2:
                    $writer->name($mapEntry->key);
                    $value = $mapEntry->value;
                    $adapter = $this->typeAdapterProvider->getAdapter($generics[1]);
                    $adapter->write($writer, $value);

                    break;
            }
        }

        $writer->endObject();
    }
}
