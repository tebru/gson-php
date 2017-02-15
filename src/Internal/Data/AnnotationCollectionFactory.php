<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal\Data;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\Cache;
use ReflectionClass;
use ReflectionMethod;
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
     * @var Reader
     */
    private $reader;

    /**
     * Cache for collection of annotations
     *
     * @var Cache
     */
    private $cache;

    /**
     * Constructor
     *
     * @param Reader $reader
     * @param Cache $cache
     */
    public function __construct(Reader $reader, Cache $cache)
    {
        $this->reader = $reader;
        $this->cache = $cache;
    }

    /**
     * Create a set of property annotations
     *
     * @param string $className
     * @param string $propertyName
     * @return AnnotationSet
     * @throws \InvalidArgumentException If the type does not exist
     */
    public function createPropertyAnnotations(string $className, string $propertyName): AnnotationSet
    {
        $key = $className.':'.$propertyName;
        if ($this->cache->contains($key)) {
            return $this->cache->fetch($key);
        }

        $reflectionProperty = new ReflectionProperty($className, $propertyName);

        $annotations = new AnnotationSet();

        // start with with all property annotations
        foreach ($this->reader->getPropertyAnnotations($reflectionProperty) as $defaultAnnotation) {
            $annotations->addAnnotation($defaultAnnotation, AnnotationSet::TYPE_PROPERTY);
        }

        $reflectionClass = $reflectionProperty->getDeclaringClass();
        $parentClass = $reflectionClass->getParentClass();

        // add all new parent annotations
        foreach ($this->reader->getClassAnnotations($reflectionClass) as $parentAnnotation) {
            $annotations->addAnnotation($parentAnnotation, AnnotationSet::TYPE_CLASS);
        }

        while (false !== $parentClass) {
            // add parent property annotations if they exist
            if ($parentClass->hasProperty($reflectionProperty->getName())) {
                $parentProperty = $parentClass->getProperty($reflectionProperty->getName());
                foreach ($this->reader->getPropertyAnnotations($parentProperty) as $parentPropertyAnnotation) {
                    $annotations->addAnnotation($parentPropertyAnnotation, AnnotationSet::TYPE_PROPERTY);
                }
            }

            // add all parent class annotations
            foreach ($this->reader->getClassAnnotations($parentClass) as $parentClassAnnotation) {
                $annotations->addAnnotation($parentClassAnnotation, AnnotationSet::TYPE_CLASS);
            }

            // reset $parentClass
            $parentClass = $parentClass->getParentClass();
        }

        $this->cache->save($key, $annotations);

        return $annotations;
    }

    /**
     * Create a set of class annotations
     *
     * @param string $className
     * @return AnnotationSet
     * @throws \InvalidArgumentException If the type does not exist
     */
    public function createClassAnnotations(string $className): AnnotationSet
    {
        if ($this->cache->contains($className)) {
            return $this->cache->fetch($className);
        }

        $reflectionClass = new ReflectionClass($className);

        $annotations = new AnnotationSet();
        foreach ($this->reader->getClassAnnotations($reflectionClass) as $annotation) {
            $annotations->addAnnotation($annotation, AnnotationSet::TYPE_CLASS);
        }
        $parentClass = $reflectionClass->getParentClass();

        while (false !== $parentClass) {
            foreach ($this->reader->getClassAnnotations($parentClass) as $parentAnnotation) {
                $annotations->addAnnotation($parentAnnotation, AnnotationSet::TYPE_CLASS);
            }
            $parentClass = $parentClass->getParentClass();
        }

        $this->cache->save($className, $annotations);

        return $annotations;
    }

    /**
     * Create a set of method annotations
     *
     * @param string $className
     * @param string $methodName
     * @return AnnotationSet
     * @throws \InvalidArgumentException If the type does not exist
     */
    public function createMethodAnnotations(string $className, string $methodName): AnnotationSet
    {
        $key = $className.':'.$methodName;
        if ($this->cache->contains($key)) {
            return $this->cache->fetch($key);
        }

        $annotations = new AnnotationSet();

        $reflectionMethod = new ReflectionMethod($className, $methodName);
        foreach ($this->reader->getMethodAnnotations($reflectionMethod) as $annotation) {
            $annotations->addAnnotation($annotation, AnnotationSet::TYPE_METHOD);
        }

        $this->cache->save($key, $annotations);

        return $annotations;
    }
}
