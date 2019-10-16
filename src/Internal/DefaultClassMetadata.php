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
use Tebru\Gson\Internal\Data\PropertyCollection;
use Tebru\Gson\PropertyMetadata;
use Tebru\Gson\PropertyMetadataCollection;

/**
 * Class DefaultClassMetadata
 *
 * Represents a class an its annotations
 *
 * This class contains public properties to improve performance.
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
    public $name;

    /**
     * The class annotations
     *
     * @var AnnotationCollection
     */
    public $annotations;

    /**
     * @var PropertyCollection
     */
    public $properties;

    /**
     * @var bool
     */
    public $skipSerialize = false;

    /**
     * @var bool
     */
    public $skipDeserialize = false;

    /**
     * Constructor
     *
     * @param string $name
     * @param AnnotationCollection $annotations
     * @param PropertyCollection $properties
     */
    public function __construct(string $name, AnnotationCollection $annotations, PropertyCollection $properties)
    {
        $this->name = $name;
        $this->annotations = $annotations;
        $this->properties = $properties;
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
     * Get the [@see PropertyMetadataCollection] for class
     *
     * @return PropertyMetadataCollection
     */
    public function getPropertyMetadataCollection(): PropertyMetadataCollection
    {
        return $this->properties;
    }

    /**
     * Get the [@see PropertyCollection] for class
     *
     * @return PropertyCollection
     */
    public function getPropertyCollection(): PropertyCollection
    {
        return $this->properties;
    }

    /**
     * Returns an array of [@see PropertyMetadata] objects
     *
     * @return PropertyMetadata[]
     */
    public function getPropertyMetadata(): array
    {
        return $this->properties->toArray();
    }

    /**
     * Get [@see PropertyMetadata] by property name
     *
     * @param string $propertyName
     * @return PropertyMetadata|null
     */
    public function getProperty(string $propertyName): ?PropertyMetadata
    {
        return $this->properties->getByName($propertyName);
    }

    /**
     * If the class should be skipped during serialization
     *
     * @return bool
     */
    public function skipSerialize(): bool
    {
        return $this->skipSerialize;
    }

    /**
     * Set if we should skip serialization
     *
     * @param bool $skipSerialize
     */
    public function setSkipSerialize(bool $skipSerialize): void
    {
        $this->skipSerialize = $skipSerialize;
    }

    /**
     * If the class should be skipped during deserialization
     *
     * @return bool
     */
    public function skipDeserialize(): bool
    {
        return $this->skipDeserialize;
    }

    /**
     * Set if we should skip deserialization
     *
     * @param bool $skipDeserialize
     */
    public function setSkipDeserialize(bool $skipDeserialize): void
    {
        $this->skipDeserialize = $skipDeserialize;
    }
}
