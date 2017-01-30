<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal\Data;

use Tebru\Collection\Bag;

/**
 * Class PropertyCollection
 *
 * A collection of [@see Property] objects
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class PropertyCollection extends Bag
{
    /**
     * Get [@see Property] by real name
     *
     * @param string $name
     * @return Property|null
     */
    public function getByName(string $name): ?Property
    {
        return $this->find(function (Property $property) use ($name) {
            return $property->getRealName() === $name;
        });
    }

    /**
     * Get [@see Property] by serialized name
     *
     * @param string $name
     * @return Property|null
     */
    public function getBySerializedName(string $name): ?Property
    {
        return $this->find(function (Property $property) use ($name) {
            return $property->getSerializedName() === $name;
        });
    }
}
