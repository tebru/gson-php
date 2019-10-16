<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal\Data;

use ArrayIterator;
use Tebru\Gson\PropertyMetadata;
use Tebru\Gson\PropertyMetadataCollection;

/**
 * Class PropertyCollection
 *
 * A collection of [@see PropertyMetadata] objects
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class PropertyCollection implements PropertyMetadataCollection
{
    /**
     * Array of [@see Property] objects
     *
     * @var Property[]
     */
    public $elements;

    /**
     * Constructor
     *
     * @param Property[] $elements
     */
    public function __construct(array $elements = [])
    {
        $this->elements = $elements;
    }

    /**
     * @param PropertyMetadata $property
     * @return void
     */
    public function add(PropertyMetadata $property): void
    {
        $this->elements[$property->getSerializedName()] = $property;
    }

    /**
     * Add a property to the collection
     *
     * @param PropertyMetadata $property
     * @return void
     */
    public function remove(PropertyMetadata $property): void
    {
        $this->removeBySerializedName($property->getSerializedName());
    }

    /**
     * Clear out collection
     */
    public function clear(): void
    {
        $this->elements = [];
    }

    /**
     * Get [@see PropertyMetadata] by property name
     *
     * @param string $propertyName
     * @return PropertyMetadata|null
     */
    public function getByName(string $propertyName): ?PropertyMetadata
    {
        foreach ($this->elements as $property) {
            if ($property->getName() === $propertyName) {
                return $property;
            }
        }

        return null;
    }

    /**
     * Remove property by name
     *
     * @param string $propertyName
     * @return void
     */
    public function removeByName(string $propertyName): void
    {
        $property = $this->getByName($propertyName);
        if ($property === null) {
            return;
        }

        $this->removeBySerializedName($property->getSerializedName());
    }

    /**
     * Get [@see Property] by serialized name
     *
     * @param string $name
     * @return PropertyMetadata|null
     */
    public function getBySerializedName(string $name): ?PropertyMetadata
    {
        if (!isset($this->elements[$name])) {
            return null;
        }

        return $this->elements[$name];
    }

    /**
     * Remove property by serialized name
     *
     * @param string $name
     * @return void
     */
    public function removeBySerializedName(string $name): void
    {
        if (!isset($this->elements[$name])) {
            return;
        }

        unset($this->elements[$name]);
    }

    /**
     * Array of Property objects
     *
     * @return Property[]
     */
    public function toArray(): array
    {
        return array_values($this->elements);
    }

    /**
     * Retrieve an external iterator
     *
     * @return ArrayIterator|Property[]
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator(array_values($this->elements));
    }
}
