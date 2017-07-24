<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson;

use Tebru\AnnotationReader\AbstractAnnotation;
use Tebru\AnnotationReader\AnnotationCollection;
use Tebru\PhpType\TypeToken;

/**
 * Interface PropertyMetadata
 *
 * Represents a property and its annotations
 *
 * @author Nate Brunette <n@tebru.net>
 */
interface PropertyMetadata
{
    /**
     * Get the property name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get the property serialized name
     *
     * @return string
     */
    public function getSerializedName(): string;

    /**
     * Get the full php type object
     *
     * @return TypeToken
     */
    public function getType(): TypeToken;

    /**
     * Get the property type as a string
     *
     * @return string
     */
    public function getTypeName(): string;

    /**
     * Get the property modifiers as a bitmap of [@see \ReflectionProperty] constants
     *
     * @return int
     */
    public function getModifiers(): int;

    /**
     * Get full declaring class metadata
     *
     * @return ClassMetadata
     */
    public function getDeclaringClassMetadata(): ClassMetadata;

    /**
     * Get the declaring class name
     *
     * @return string
     */
    public function getDeclaringClassName(): string;

    /**
     * Get property annotations
     *
     * @return AnnotationCollection
     */
    public function getAnnotations(): AnnotationCollection;

    /**
     * Get a single annotation, returns null if the annotation doesn't exist
     *
     * @param string $annotationName
     * @return null|AbstractAnnotation
     */
    public function getAnnotation(string $annotationName): ?AbstractAnnotation;

    /**
     * Returns true if the property is virtual
     *
     * @return bool
     */
    public function isVirtual(): bool;
}
