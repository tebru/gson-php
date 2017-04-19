<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson;

use Tebru\Gson\Internal\Data\AnnotationSet;


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
     * @return AnnotationSet
     */
    public function getAnnotations(): AnnotationSet;

    /**
     * Get a specific annotation by class name, returns null if the annotation
     * doesn't exist.
     *
     * @param string $annotationClass
     * @return null|object
     */
    public function getAnnotation(string $annotationClass);
}
