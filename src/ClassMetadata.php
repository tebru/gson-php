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
final class ClassMetadata
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
}
