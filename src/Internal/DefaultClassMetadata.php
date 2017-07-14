<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal;

use Tebru\Gson\ClassMetadata;
use Tebru\Gson\Internal\Data\AnnotationSet;
use Tebru\Gson\PropertyMetadata;

/**
 * Class DefaultClassMetadata
 *
 * Represents a class an its annotations
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class DefaultClassMetadata implements ClassMetadata
{
    /**
     * The class name
     *
     * @var string
     */
    private $name;

    /**
     * The class annotations
     *
     * @var AnnotationSet
     */
    private $annotations;

    /**
     * An array of [@see PropertyMetadata] objects
     *
     * @var PropertyMetadata[]
     */
    private $propertyMetadata;

    /**
     * Constructor
     *
     * @param string $name
     * @param AnnotationSet $annotations
     */
    public function __construct(string $name, AnnotationSet $annotations)
    {
        $this->name = $name;
        $this->annotations = $annotations;
    }

    /**
     * Get the class name as a string
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get all class annotations
     *
     * @return AnnotationSet
     */
    public function getAnnotations(): AnnotationSet
    {
        return $this->annotations;
    }

    /**
     * Get a specific annotation by class name, returns null if the annotation
     * doesn't exist.
     *
     * @param string $annotationClass
     * @return null|object
     */
    public function getAnnotation(string $annotationClass)
    {
        return $this->annotations->getAnnotation($annotationClass, AnnotationSet::TYPE_CLASS);
    }

    /**
     * Returns an array of [@see PropertyMetadata] objects
     *
     * @return array
     */
    public function getPropertyMetadata(): array
    {
        return $this->propertyMetadata;
    }

    /**
     * Get [@see PropertyMetadata] by property name
     *
     * @param string $propertyName
     * @return PropertyMetadata|null
     */
    public function getProperty(string $propertyName): ?PropertyMetadata
    {
        foreach ($this->propertyMetadata as $property) {
            if ($property->getName() === $propertyName) {
                return $property;
            }
        }

        return null;
    }

    /**
     * Add [@see PropertyMetadata] link
     *
     * @param PropertyMetadata $propertyMetadata
     * @return void
     */
    public function addPropertyMetadata(PropertyMetadata $propertyMetadata): void
    {
        $this->propertyMetadata[] = $propertyMetadata;
    }
}
