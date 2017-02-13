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
        $key = $className.':'.$propertyName;
        if ($this->cache->contains($key)) {
            return $this->cache->fetch($key);
        }

        $reflectionProperty = new ReflectionProperty($className, $propertyName);

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

        $annotations = new AnnotationSet($this->reader->getClassAnnotations($reflectionClass));
        $parentClass = $reflectionClass->getParentClass();

        while (false !== $parentClass) {
            $annotations->addAllArray($this->reader->getClassAnnotations($parentClass));
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
        $annotations->addAllArray($this->reader->getMethodAnnotations($reflectionMethod));

        $this->cache->save($key, $annotations);

        return $annotations;
    }
}
