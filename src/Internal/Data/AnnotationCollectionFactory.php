<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal\Data;

use Doctrine\Common\Annotations\AnnotationReader;
use ReflectionClass;
use ReflectionProperty;

/**
 * Class AnnotationCollectionFactory
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class AnnotationCollectionFactory
{
    /**
     * Doctrine annotation reader
     *
     * @var AnnotationReader
     */
    private $reader;

    /**
     * Constructor
     *
     * @param AnnotationReader $reader
     */
    public function __construct(AnnotationReader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Create a set of property annotations
     *
     * @param ReflectionProperty $reflectionProperty
     * @return AnnotationSet
     */
    public function createPropertyAnnotations(ReflectionProperty $reflectionProperty): AnnotationSet
    {
        // start with with all property annotations
        $annotations = new AnnotationSet($this->reader->getPropertyAnnotations($reflectionProperty));

        $reflectionClass = $reflectionProperty->getDeclaringClass();
        $parentClass = $reflectionClass->getParentClass();

        // add all new parent annotations
        $annotations->addAllArray($this->reader->getClassAnnotations($reflectionClass));

        while (false !== $parentClass) {
            // add parent property annotations if they exist
            if ($parentClass->hasProperty($reflectionProperty->getName())) {
                $parentProperty = $parentClass->getProperty($reflectionProperty->getName());
                $annotations->addAllArray($this->reader->getPropertyAnnotations($parentProperty));
            }

            // add all parent class annotations
            $annotations->addAllArray($this->reader->getClassAnnotations($parentClass));

            // reset $parentClass
            $parentClass = $parentClass->getParentClass();
        }

        return $annotations;
    }

    /**
     * Create a set of class annotations
     *
     * @param ReflectionClass $reflectionClass
     * @return AnnotationSet
     */
    public function createClassAnnotations(ReflectionClass $reflectionClass): AnnotationSet
    {
        $annotations = new AnnotationSet($this->reader->getClassAnnotations($reflectionClass));
        $parentClass = $reflectionClass->getParentClass();

        while (false !== $parentClass) {
            $annotations->addAllArray($this->reader->getClassAnnotations($parentClass));
            $parentClass = $parentClass->getParentClass();
        }

        return $annotations;
    }
}
