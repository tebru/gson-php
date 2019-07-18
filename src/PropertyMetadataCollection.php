<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
declare(strict_types=1);

namespace Tebru\Gson;

use IteratorAggregate;

/**
 * Interface PropertyMetadataCollection
 *
 * @author Nate Brunette <n@tebru.net>
 */
interface PropertyMetadataCollection extends IteratorAggregate
{
    /**
     * Add a property to the collection
     *
     * @param PropertyMetadata $property
     * @return void
     */
    public function add(PropertyMetadata $property): void;

    /**
     * Add a property to the collection
     *
     * @param PropertyMetadata $property
     * @return void
     */
    public function remove(PropertyMetadata $property): void;

    /**
     * Clear out collection
     */
    public function clear(): void;

    /**
     * Get by property name
     *
     * @param string $propertyName
     * @return PropertyMetadata|null
     */
    public function getByName(string $propertyName): ?PropertyMetadata;

    /**
     * Remove property by name
     *
     * @param string $propertyName
     * @return void
     */
    public function removeByName(string $propertyName):void;

    /**
     * Get property by serialized name
     *
     * @param string $name
     * @return PropertyMetadata|null
     */
    public function getBySerializedName(string $name): ?PropertyMetadata;

    /**
     * Remove property by serialized name
     *
     * @param string $name
     * @return void
     */
    public function removeBySerializedName(string $name): void;

    /**
     * Array of Property objects
     *
     * @return PropertyMetadata[]
     */
    public function toArray(): array;
}
