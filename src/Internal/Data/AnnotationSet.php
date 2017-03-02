<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal\Data;

use InvalidArgumentException;

/**
 * Class ClassNameSet
 *
 * A HashSet that is keyed by class name
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class AnnotationSet
{
    const TYPE_CLASS = 1;
    const TYPE_PROPERTY = 2;
    const TYPE_METHOD = 4;

    /**
     * Class annotations
     *
     * @var array
     */
    private $classAnnotations = [];

    /**
     * Property annotations
     *
     * @var array
     */
    private $propertyAnnotations = [];

    /**
     * Method annotations
     *
     * @var array
     */
    private $methodAnnotations = [];

    /**
     * Add an annotation by type
     *
     * @param $annotation
     * @param int $type
     * @throws \InvalidArgumentException If the type does not exist
     */
    public function addAnnotation($annotation, int $type)
    {
        $class = get_class($annotation);
        switch ($type) {
            case self::TYPE_CLASS:
                if (isset($this->classAnnotations[$class])) {
                    return;
                }

                $this->classAnnotations[$class] = $annotation;
                break;
            case self::TYPE_PROPERTY:
                if (isset($this->propertyAnnotations[$class])) {
                    return;
                }

                $this->propertyAnnotations[$class] = $annotation;
                break;
            case self::TYPE_METHOD:
                if (isset($this->methodAnnotations[$class])) {
                    return;
                }

                $this->methodAnnotations[$class] = $annotation;
                break;
            default:
                throw new InvalidArgumentException('Type not supported');
        }
    }

    /**
     * Get an annotation by class name
     *
     * @param string $annotationClass
     * @param int $filter
     * @return null|object
     */
    public function getAnnotation(string $annotationClass, $filter)
    {
        if (self::TYPE_PROPERTY & $filter && isset($this->propertyAnnotations[$annotationClass])) {
            return $this->propertyAnnotations[$annotationClass];
        }

        if (self::TYPE_CLASS & $filter && isset($this->classAnnotations[$annotationClass])) {
            return $this->classAnnotations[$annotationClass];
        }

        if (self::TYPE_METHOD & $filter && isset($this->methodAnnotations[$annotationClass])) {
            return $this->methodAnnotations[$annotationClass];
        }

        return null;
    }

    /**
     * Get an array of a specific type of annotation
     *
     * @param int $type
     * @return array
     * @throws \InvalidArgumentException If the type does not exist
     */
    public function toArray(int $type)
    {
        if (self::TYPE_CLASS === $type) {
            return array_values($this->classAnnotations);
        }

        if (self::TYPE_PROPERTY === $type) {
            return array_values($this->propertyAnnotations);
        }

        if (self::TYPE_METHOD === $type) {
            return array_values($this->methodAnnotations);
        }

        throw new InvalidArgumentException('Type not supported');
    }
}
