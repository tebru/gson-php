<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson;

use Tebru\Gson\Element\JsonElement;
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
     * @param mixed $object
     * @return string
     */
    public function toJson($object): string
    {
        $type = TypeToken::createFromVariable($object);
        $typeAdapter = $this->typeAdapterProvider->getAdapter($type);

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
        $typeToken = $isObject ? new TypeToken(\get_class($type)) : new TypeToken($type);
        $typeAdapter = $this->typeAdapterProvider->getAdapter($typeToken);

        if ($isObject && $typeAdapter instanceof ObjectConstructorAware) {
            $typeAdapter->setObjectConstructor(new CreateFromInstance($type));
        }

        return $typeAdapter->readFromJson($json);
    }

    /**
     * Converts an object to a [@see JsonElement]
     *
     * This is a convenience method that first converts an object to json utilizing all of the
     * type adapters, then converts that json to a JsonElement.  From here you can modify the
     * JsonElement and call json_encode() on it to get json.
     *
     * @param mixed $object
     * @return JsonElement
     */
    public function toJsonElement($object): JsonElement
    {
        return $this->fromJson($this->toJson($object), JsonElement::class);
    }

    /**
     * Convenience method to convert an object to a json_decode'd array
     *
     * @param object $object
     * @return array
     */
    public function toArray($object): array
    {
        return (array)\json_decode($this->toJson($object), true);
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
