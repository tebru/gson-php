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
     */
    public function createPropertyAnnotations(string $className, string $propertyName): AnnotationSet
    {
        $key = 'annotations:'.$className.':'.$propertyName;
        if ($this->cache->contains($key)) {
            return $this->cache->fetch($key);
        }

        $reflectionProperty = new ReflectionProperty($className, $propertyName);

        $annotations = new AnnotationSet();

        // start with with all property annotations
        foreach ($this->reader->getPropertyAnnotations($reflectionProperty) as $defaultAnnotation) {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $annotations->addAnnotation($defaultAnnotation, AnnotationSet::TYPE_PROPERTY);
        }

        $reflectionClass = $reflectionProperty->getDeclaringClass();
        $parentClass = $reflectionClass->getParentClass();

        while (false !== $parentClass) {
            // add parent property annotations if they exist
            if ($parentClass->hasProperty($reflectionProperty->getName())) {
                $parentProperty = $parentClass->getProperty($reflectionProperty->getName());
                foreach ($this->reader->getPropertyAnnotations($parentProperty) as $parentPropertyAnnotation) {
                    /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
                    $annotations->addAnnotation($parentPropertyAnnotation, AnnotationSet::TYPE_PROPERTY);
                }
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
     */
    public function createClassAnnotations(string $className): AnnotationSet
    {
        if ($this->cache->contains($className)) {
            return $this->cache->fetch($className);
        }

        $reflectionClass = new ReflectionClass($className);

        $annotations = new AnnotationSet();
        foreach ($this->reader->getClassAnnotations($reflectionClass) as $annotation) {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $annotations->addAnnotation($annotation, AnnotationSet::TYPE_CLASS);
        }
        $parentClass = $reflectionClass->getParentClass();

        while (false !== $parentClass) {
            foreach ($this->reader->getClassAnnotations($parentClass) as $parentAnnotation) {
                /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
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
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $annotations->addAnnotation($annotation, AnnotationSet::TYPE_METHOD);
        }

        $parentClass = $reflectionMethod->getDeclaringClass()->getParentClass();
        while (false !== $parentClass) {
            // add parent property annotations if they exist
            if ($parentClass->hasMethod($reflectionMethod->getName())) {
                $parentMethod = $parentClass->getMethod($reflectionMethod->getName());
                foreach ($this->reader->getMethodAnnotations($parentMethod) as $parentMethodAnnotation) {
                    /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
                    $annotations->addAnnotation($parentMethodAnnotation, AnnotationSet::TYPE_METHOD);
                }
            }

            // reset $parentClass
            $parentClass = $parentClass->getParentClass();
        }

        $this->cache->save($key, $annotations);

        return $annotations;
    }
}
