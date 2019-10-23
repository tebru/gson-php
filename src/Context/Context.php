<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
declare(strict_types=1);

namespace Tebru\Gson\Context;

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
    protected $attributes = [];

    /**
     * If the default scalar type adapters should be enabled
     *
     * @var bool
     */
    protected $enableScalarAdapters = true;

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
}
