<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal;

use ReflectionMethod;
use ReflectionProperty;
use Tebru\Gson\Internal\AccessorStrategy\GetByClosure;
use Tebru\Gson\Internal\AccessorStrategy\GetByMethod;
use Tebru\Gson\Internal\AccessorStrategy\GetByPublicProperty;
use Tebru\Gson\Internal\AccessorStrategy\SetByClosure;
use Tebru\Gson\Internal\AccessorStrategy\SetByMethod;
use Tebru\Gson\Internal\AccessorStrategy\SetByPublicProperty;

/**
 * Class AccessorStrategyFactory
 *
 * Creates the strategy that will be used to get or set values
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class AccessorStrategyFactory
{
    /**
     * Create strategy to access a value
     *
     * - If there's a method, use the method
     * - If the property is public, use the property
     * - Bind a closure
     *
     * @param ReflectionProperty $reflectionProperty
     * @param ReflectionMethod|null $getterMethod
     * @return GetterStrategy
     */
    public function getterStrategy(ReflectionProperty $reflectionProperty, ReflectionMethod $getterMethod = null): GetterStrategy
    {
        if (null !== $getterMethod) {
            return new GetByMethod($getterMethod->getName());
        }

        if ($reflectionProperty->isPublic()) {
            return new GetByPublicProperty($reflectionProperty->getName());
        }

        return new GetByClosure($reflectionProperty->getName(), $reflectionProperty->getDeclaringClass()->getName());
    }

    /**
     * Create strategy to set a value
     *
     * - If there's a method, use the method
     * - If the property is public, use the property
     * - Bind a closure
     *
     * @param ReflectionProperty $reflectionProperty
     * @param ReflectionMethod|null $setterMethod
     * @return SetterStrategy
     */
    public function setterStrategy(ReflectionProperty $reflectionProperty, ReflectionMethod $setterMethod = null): SetterStrategy
    {
        if (null !== $setterMethod) {
            return new SetByMethod($setterMethod->getName());
        }

        if ($reflectionProperty->isPublic()) {
            return new SetByPublicProperty($reflectionProperty->getName());
        }

        return new SetByClosure($reflectionProperty->getName(), $reflectionProperty->getDeclaringClass()->getName());
    }
}
