<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal\Naming;

use Tebru\AnnotationReader\AnnotationCollection;
use Tebru\Gson\Annotation\SerializedName;
use Tebru\Gson\Annotation\VirtualProperty;
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
     * @param AnnotationCollection $annotations
     * @return string
     */
    public function serializedName(string $propertyName, AnnotationCollection $annotations): string
    {
        $serializedName = $annotations->get(SerializedName::class);
        if (null !== $serializedName) {
            return $serializedName->getValue();
        }

        /** @var VirtualProperty $virtualProperty */
        $virtualProperty = $annotations->get(VirtualProperty::class);
        if ($virtualProperty !== null && $virtualProperty->getSerializedName() !== null) {
            return $virtualProperty->getSerializedName();
        }

        return $this->propertyNamingStrategy->translateName($propertyName);
    }
}
