<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson;

use Tebru\Gson\Internal\Data\AnnotationSet;
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
     * @return AnnotationSet
     */
    public function getAnnotations(): AnnotationSet;

    /**
     * Get a single annotation, returns null if the annotation doesn't exist
     *
     * @param string $annotationName
     * @return null|object
     */
    public function getAnnotation(string $annotationName);

    /**
     * Returns true if the property is virtual
     *
     * @return bool
     */
    public function isVirtual(): bool;
}
