<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson;

use Tebru\Gson\Context\ReaderContext;
use Tebru\Gson\Context\WriterContext;
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
     * @var ReaderContext
     */
    private $readerContext;

    /**
     * @var WriterContext
     */
    private $writerContext;

    /**
     * Constructor
     *
     * @param TypeAdapterProvider $typeAdapterProvider
     * @param ReaderContext $readerContext
     * @param WriterContext $writerContext
     */
    public function __construct(
        TypeAdapterProvider $typeAdapterProvider,
        ReaderContext $readerContext,
        WriterContext $writerContext
    ) {
        $this->typeAdapterProvider = $typeAdapterProvider;
        $this->readerContext = $readerContext;
        $this->writerContext = $writerContext;
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
     * Convenience method to convert an object to normalized data
     *
     * Optionally accepts a type to force serialization to
     *
     * @param mixed $object
     * @param null|string $type
     * @return mixed
     */
    public function toNormalized($object, ?string $type = null)
    {
        if (is_scalar($object) && !$this->writerContext->enableScalarAdapters()) {
            return $object;
        }

        $typeToken = $type === null ? TypeToken::createFromVariable($object) : TypeToken::create($type);
        $typeAdapter = $this->typeAdapterProvider->getAdapter($typeToken);

        return $typeAdapter->write($object, $this->writerContext);
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
        return json_encode($this->toNormalized($object, $type));
    }

    /**
     * Converts a normalized data to a valid json type
     *
     * @param mixed $decodedJson
     * @param object|string $type
     * @return mixed
     */
    public function fromNormalized($decodedJson, $type)
    {
        if (is_scalar($decodedJson) && !$this->readerContext->enableScalarAdapters()) {
            return $decodedJson;
        }

        $isObject = is_object($type);
        $typeToken = $isObject ? TypeToken::create(get_class($type)) : TypeToken::create($type);
        $typeAdapter = $this->typeAdapterProvider->getAdapter($typeToken);
        $this->readerContext->setUsesExistingObject($isObject);
        $this->readerContext->setPayload($decodedJson);

        if ($isObject && $typeAdapter instanceof ObjectConstructorAware) {
            $typeAdapter->setObjectConstructor(new CreateFromInstance($type));
        }

        return $typeAdapter->read($decodedJson, $this->readerContext);
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
        return $this->fromNormalized(json_decode($json, true), $type);
    }
}
