<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal\Data;

use ReflectionClass;
use ReflectionProperty;

/**
 * Class ReflectionPropertySetFactory
 *
 * Create a set of reflection properties, preferring properties of child classes
 * over parent classes.
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class ReflectionPropertySetFactory
{
    /**
     * Get a flat list of all properties in class and parent classes
     *
     * @param ReflectionClass $reflectionClass
     * @return ReflectionPropertySet
     */
    public function create(ReflectionClass $reflectionClass): ReflectionPropertySet
    {
        $properties = new ReflectionPropertySet();
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $properties->add($reflectionProperty);
        }

        $parentClass = $reflectionClass->getParentClass();

        while (false !== $parentClass) {
            // add all private properties from parent
            foreach ($parentClass->getProperties(ReflectionProperty::IS_PRIVATE) as $property) {
                $properties->add($property);
            }

            // reset $parentClass
            $parentClass = $parentClass->getParentClass();
        }

        return $properties;
    }
}
