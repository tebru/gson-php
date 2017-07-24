<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal;

use Tebru\AnnotationReader\AbstractAnnotation;
use Tebru\AnnotationReader\AnnotationCollection;
use Tebru\Gson\ClassMetadata;
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
     * @var AnnotationCollection
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
     * @param AnnotationCollection $annotations
     */
    public function __construct(string $name, AnnotationCollection $annotations)
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
     * @return AnnotationCollection
     */
    public function getAnnotations(): AnnotationCollection
    {
        return $this->annotations;
    }

    /**
     * Get a specific annotation by class name, returns null if the annotation
     * doesn't exist.
     *
     * @param string $annotationClass
     * @return null|AbstractAnnotation
     */
    public function getAnnotation(string $annotationClass): ?AbstractAnnotation
    {
        return $this->annotations->get($annotationClass);
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
