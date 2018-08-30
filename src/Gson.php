<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson;

use Tebru\Gson\Element\JsonElement;
use Tebru\Gson\Internal\DefaultReaderContext;
use Tebru\Gson\Internal\ObjectConstructor\CreateFromInstance;
use Tebru\Gson\Internal\ObjectConstructorAware;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\PhpType\TypeToken;

/**
 * Class Gson
 *
 * @author Nate Brunette <n@tebru.net>
 */
class Gson
{
    /**
     * A service to fetch the correct [@see TypeAdapter] for a given type
     *
     * @var TypeAdapterProvider
     */
    private $typeAdapterProvider;

    /**
     * True if we should serialize nulls
     *
     * @var bool
     */
    private $serializeNull;

    /**
     * Constructor
     *
     * @param TypeAdapterProvider $typeAdapterProvider
     * @param bool $serializeNull
     */
    public function __construct(TypeAdapterProvider $typeAdapterProvider, bool $serializeNull)
    {
        $this->typeAdapterProvider = $typeAdapterProvider;
        $this->serializeNull = $serializeNull;
    }

    /**
     * Create a new builder object
     *
     * @return GsonBuilder
     */
    public static function builder(): GsonBuilder
    {
        return new GsonBuilder();
    }

    /**
     * Converts an object to a json string
     *
     * Optionally accepts a type to force serialization to
     *
     * @param mixed $object
     * @param null|string $type
     * @return string
     */
    public function toJson($object, ?string $type = null): string
    {
        $typeToken = $type === null ? TypeToken::createFromVariable($object) : TypeToken::create($type);
        $typeAdapter = $this->typeAdapterProvider->getAdapter($typeToken);

        return $typeAdapter->writeToJson($object, $this->serializeNull);
    }

    /**
     * Converts a json string to a valid json type
     *
     * @param string $json
     * @param object|string $type
     * @return mixed
     */
    public function fromJson(string $json, $type)
    {
        $isObject = \is_object($type);
        $typeToken = $isObject ? TypeToken::create(\get_class($type)) : TypeToken::create($type);
        $typeAdapter = $this->typeAdapterProvider->getAdapter($typeToken);
        $context = new DefaultReaderContext();
        $context->setUsesExistingObject($isObject);

        if ($isObject && $typeAdapter instanceof ObjectConstructorAware) {
            $typeAdapter->setObjectConstructor(new CreateFromInstance($type));
        }

        return $typeAdapter->readFromJson($json, $context);
    }

    /**
     * Converts an object to a [@see JsonElement]
     *
     * Optionally accepts a type to force serialization to
     *
     * @param mixed $object
     * @param null|string $type
     * @return JsonElement
     */
    public function toJsonElement($object, ?string $type = null): JsonElement
    {
        $typeToken = $type === null ? TypeToken::createFromVariable($object) : TypeToken::create($type);
        $typeAdapter = $this->typeAdapterProvider->getAdapter($typeToken);

        return $typeAdapter->writeToJsonElement($object, $this->serializeNull);
    }

    /**
     * Convenience method to convert an object to a json_decode'd array
     *
     * Optionally accepts a type to force serialization to
     *
     * @param mixed $object
     * @param null|string $type
     * @return array
     */
    public function toArray($object, ?string $type = null): array
    {
        return (array)\json_decode($this->toJson($object, $type), true);
    }

    /**
     * Convenience method to deserialize an array into an object
     *
     * @param array $jsonArray
     * @param mixed $type
     * @return mixed
     */
    public function fromArray(array $jsonArray, $type)
    {
        return $this->fromJson(\json_encode($jsonArray), $type);
    }
}
