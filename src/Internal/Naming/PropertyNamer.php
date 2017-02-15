<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal\Naming;

use Tebru\Gson\Annotation\SerializedName;
use Tebru\Gson\Internal\Data\AnnotationSet;
use Tebru\Gson\PropertyNamingStrategy;

/**
 * Class PropertyNamer
 *
 * Gets the property name from annotation or naming strategy
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class PropertyNamer
{
    /**
     * @var PropertyNamingStrategy
     */
    private $propertyNamingStrategy;

    /**
     * Constructor
     *
     * @param PropertyNamingStrategy $propertyNamingStrategy
     */
    public function __construct(PropertyNamingStrategy $propertyNamingStrategy)
    {
        $this->propertyNamingStrategy = $propertyNamingStrategy;
    }

    /**
     * Get the serialized version of the property name
     *
     * @param string $propertyName
     * @param AnnotationSet $annotations
     * @param int $filter
     * @return string
     */
    public function serializedName(string $propertyName, AnnotationSet $annotations, int $filter): string
    {
        $serializedName = $annotations->getAnnotation(SerializedName::class, $filter);
        if (null !== $serializedName) {
            return $serializedName->getName();
        }

        return $this->propertyNamingStrategy->translateName($propertyName);
    }
}
