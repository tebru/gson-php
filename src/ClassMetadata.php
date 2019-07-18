<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson;

use Tebru\AnnotationReader\AbstractAnnotation;
use Tebru\AnnotationReader\AnnotationCollection;

/**
 * Class ClassMetadata
 *
 * Represents a class an its annotations
 *
 * @author Nate Brunette <n@tebru.net>
 */
interface ClassMetadata
{
    /**
     * Get the class name as a string
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get all class annotations
     *
     * @return AnnotationCollection
     */
    public function getAnnotations(): AnnotationCollection;

    /**
     * Get a specific annotation by class name, returns null if the annotation
     * doesn't exist.
     *
     * @param string $annotationClass
     * @return null|AbstractAnnotation
     */
    public function getAnnotation(string $annotationClass): ?AbstractAnnotation;

    /**
     * Get the [@see PropertyMetadataCollection] for class
     *
     * @return PropertyMetadataCollection
     */
    public function getPropertyMetadataCollection(): PropertyMetadataCollection;

    /**
     * Returns an array of [@see PropertyMetadata] objects
     *
     * @deprecated In favor of getPropertyMetadataCollection()
     * @return PropertyMetadata[]
     */
    public function getPropertyMetadata(): array;

    /**
     * Get [@see PropertyMetadata] by property name
     *
     * @param string $propertyName
     * @return PropertyMetadata|null
     */
    public function getProperty(string $propertyName): ?PropertyMetadata;

    /**
     * If the class should be skipped during serialization
     *
     * @return bool
     */
    public function skipSerialize(): bool;

    /**
     * Set if we should skip serialization
     *
     * @param bool $skipSerialize
     */
    public function setSkipSerialize(bool $skipSerialize): void;

    /**
     * If the class should be skipped during deserialization
     *
     * @return bool
     */
    public function skipDeserialize(): bool;

    /**
     * Set if we should skip deserialization
     *
     * @param bool $skipDeserialize
     */
    public function setSkipDeserialize(bool $skipDeserialize): void;
}
