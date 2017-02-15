<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal;

use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Tebru\Gson\Annotation\Accessor;
use Tebru\Gson\Internal\Data\AnnotationSet;
use Tebru\Gson\MethodNamingStrategy;

/**
 * Class AccessorMethodProvider
 *
 * Gets a getter or setter given a [@see ReflectionClass]
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class AccessorMethodProvider
{
    /**
     * @var MethodNamingStrategy
     */
    private $methodNamingStrategy;

    /**
     * Constructor
     *
     * @param MethodNamingStrategy $methodNamingStrategy
     */
    public function __construct(MethodNamingStrategy $methodNamingStrategy)
    {
        $this->methodNamingStrategy = $methodNamingStrategy;
    }

    /**
     * Returns a [@see ReflectionMethod] if the method exists anywhere in the class
     * hierarchy, otherwise returns null
     *
     * @param ReflectionClass $reflectionClass
     * @param ReflectionProperty $reflectionProperty
     * @param AnnotationSet $annotations
     * @return null|ReflectionMethod
     */
    public function getterMethod(ReflectionClass $reflectionClass, ReflectionProperty $reflectionProperty, AnnotationSet $annotations): ?ReflectionMethod
    {
        /** @var Accessor $accessorAnnotation */
        $accessorAnnotation = $annotations->getAnnotation(Accessor::class, AnnotationSet::TYPE_PROPERTY);
        $getters = null !== $accessorAnnotation && null !== $accessorAnnotation->getter()
            ? [$accessorAnnotation->getter()]
            : $this->methodNamingStrategy->translateToGetter($reflectionProperty->getName());

        return $this->reflectionClassMethod($reflectionClass, $getters);
    }

    /**
     * Returns a [@see ReflectionMethod] if the method exists anywhere in the class
     * hierarchy, otherwise returns null
     *
     * @param ReflectionClass $reflectionClass
     * @param ReflectionProperty $reflectionProperty
     * @param AnnotationSet $annotations
     * @return null|ReflectionMethod
     */
    public function setterMethod(ReflectionClass $reflectionClass, ReflectionProperty $reflectionProperty, AnnotationSet $annotations): ?ReflectionMethod
    {
        /** @var Accessor $accessorAnnotation */
        $accessorAnnotation = $annotations->getAnnotation(Accessor::class, AnnotationSet::TYPE_PROPERTY);
        $setters = null !== $accessorAnnotation && null !== $accessorAnnotation->setter()
            ? [$accessorAnnotation->setter()]
            : $this->methodNamingStrategy->translateToSetter($reflectionProperty->getName());

        return $this->reflectionClassMethod($reflectionClass, $setters);
    }

    /**
     * Attempts to find the first method in an array of methods in a class.  The method is
     * only returned if it's public.
     *
     * @param ReflectionClass $reflectionClass
     * @param array $accessors
     * @return null|ReflectionMethod
     */
    private function reflectionClassMethod(ReflectionClass $reflectionClass, array $accessors): ?ReflectionMethod
    {
        foreach ($accessors as $method) {
            if (!$reflectionClass->hasMethod($method)) {
                continue;
            }

            $reflectionMethod = $reflectionClass->getMethod($method);

            if (!$reflectionMethod->isPublic()) {
                continue;
            }

            return $reflectionMethod;
        }

        return null;
    }
}
