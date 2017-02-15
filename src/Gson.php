<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson;

use Tebru\Gson\Internal\PhpType;
use Tebru\Gson\Internal\Data\Property;
use Tebru\Gson\Internal\Data\PropertyCollectionFactory;
use Tebru\Gson\Internal\TypeAdapterProvider;

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
     * A factory that reflects over class properties and returns a collection
     * of [@see Property] objects
     *
     * @var \Tebru\Gson\Internal\Data\PropertyCollectionFactory
     */
    private $propertyCollectionFactory;

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
     * @param PropertyCollectionFactory $propertyCollectionFactory
     * @param bool $serializeNull
     */
    public function __construct(
        TypeAdapterProvider $typeAdapterProvider,
        PropertyCollectionFactory $propertyCollectionFactory,
        bool $serializeNull
    ) {
        $this->typeAdapterProvider = $typeAdapterProvider;
        $this->propertyCollectionFactory = $propertyCollectionFactory;
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
     * @param object $object
     * @return string
     */
    public function toJson($object): string
    {
    }

    /**
     * Converts a json string to an object
     *
     * @param string $json
     * @param object|string $type
     * @return object
     * @throws \Tebru\Gson\Exception\MalformedTypeException If the type cannot be parsed
     * @throws \InvalidArgumentException if the type cannot be handled by a type adapter
     * @throws \RuntimeException If the value is not valid
     * @throws \Tebru\Gson\Exception\MalformedTypeException If the type cannot be parsed
     * @throws \InvalidArgumentException if the type cannot be handled by a type adapter
     */
    public function fromJson(string $json, $type)
    {
        $phpType = is_object($type) ? new PhpType(get_class($type)) : new PhpType($type);
        $typeAdapter = $this->typeAdapterProvider->getAdapter($phpType);
        $instance = $typeAdapter->readFromJson($json);

        if (is_string($type)) {
            return $instance;
        }

        $properties = $this->propertyCollectionFactory->create($phpType, $this->typeAdapterProvider);

        /** @var Property $property */
        foreach ($properties as $property) {
            $property->set($type, $property->get($instance));
        }

        return $type;
    }
}
