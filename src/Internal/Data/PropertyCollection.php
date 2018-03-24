<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal\Data;

use ArrayIterator;
use IteratorAggregate;

/**
 * Class PropertyCollection
 *
 * A collection of [@see Property] objects
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class PropertyCollection implements IteratorAggregate
{
    /**
     * Array of [@see Property] objects
     *
     * @var Property[]
     */
    private $elements = [];

    /**
     * Constructor
     *
     * @param array $properties
     */
    public function __construct(array $properties = [])
    {
        foreach ($properties as $property) {
            $this->add($property);
        }
    }

    /**
     * @param Property $property
     * @return void
     */
    public function add(Property $property): void
    {
        $this->elements[$property->getSerializedName()] = $property;
    }

    /**
     * Get [@see Property] by serialized name
     *
     * @param string $name
     * @return Property|null
     */
    public function getBySerializedName(string $name): ?Property
    {
        if (!isset($this->elements[$name])) {
            return null;
        }

        return $this->elements[$name];
    }

    /**
     * Array of Property objects
     *
     * @return Property[]
     */
    public function toArray(): array
    {
        return \array_values($this->elements);
    }

    /**
     * Retrieve an external iterator
     *
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->toArray());
    }
}
