<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
declare(strict_types=1);

namespace Tebru\Gson\Context;

use DateTime;
use Tebru\Gson\Internal\Excluder;
use Tebru\Gson\Internal\TypeAdapterProvider;

/**
 * Class Context
 *
 * @author Nate Brunette <n@tebru.net>
 */
abstract class Context
{
    /**
     * User defined attributes
     *
     * @var mixed[]
     */
    public $attributes = [];

    /**
     * If the default scalar type adapters should be enabled
     *
     * @var bool
     */
    public $enableScalarAdapters = true;

    /**
     * @var Excluder
     */
    public $excluder;

    /**
     * @var TypeAdapterProvider
     */
    public $typeAdapterProvider;

    /**
     * @var string
     */
    public $dateFormat = DateTime::ATOM;

    /**
     * Get an array of user defined attributes
     *
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Returns the attribute value for a given key or null if it's missing
     *
     * @param string $key
     * @return mixed
     */
    public function getAttribute(string $key)
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * Add a custom attribute
     *
     * @param string $key
     * @param $value
     * @return Context
     */
    public function setAttribute(string $key, $value): Context
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * If scalar values should get sent through the type adapter
     * system or if they should just be read/written in place.
     *
     * @return bool
     */
    public function enableScalarAdapters(): bool
    {
        return $this->enableScalarAdapters;
    }

    /**
     * Defaults to true. Set to false if scalar values should be read/written
     * through a type adapter.
     *
     * @param bool $enable
     * @return Context
     */
    public function setEnableScalarAdapters(bool $enable): Context
    {
        $this->enableScalarAdapters = $enable;

        return $this;
    }

    /**
     * @return TypeAdapterProvider
     */
    public function getTypeAdapterProvider(): TypeAdapterProvider
    {
        return $this->typeAdapterProvider;
    }

    /**
     * @param TypeAdapterProvider $typeAdapterProvider
     * @return Context
     */
    public function setTypeAdapterProvider(TypeAdapterProvider $typeAdapterProvider): Context
    {
        $this->typeAdapterProvider = $typeAdapterProvider;

        return $this;
    }

    /**
     * @return Excluder
     */
    public function getExcluder(): Excluder
    {
        return $this->excluder;
    }

    /**
     * @param Excluder $excluder
     * @return Context
     */
    public function setExcluder(Excluder $excluder): Context
    {
        $this->excluder = $excluder;

        return $this;
    }

    /**
     * @return string
     */
    public function getDateFormat(): string
    {
        return $this->dateFormat;
    }

    /**
     * @param string $dateFormat
     * @return Context
     */
    public function setDateFormat(string $dateFormat): Context
    {
        $this->dateFormat = $dateFormat;

        return $this;
    }
}
