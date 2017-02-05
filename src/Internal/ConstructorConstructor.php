<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal;

use ReflectionClass;
use Tebru\Gson\InstanceCreator;
use Tebru\Gson\Internal\ObjectConstructor\CreateFromInstanceCreator;
use Tebru\Gson\Internal\ObjectConstructor\CreateFromReflectionClass;
use Tebru\Gson\Internal\ObjectConstructor\CreateWithoutArguments;
use Throwable;

/**
 * Class ConstructorConstructor
 *
 * This class acts as an ObjectConstructor factory.  It takes in a map of instance creators and
 * wraps object creation in an [@see ObjectConstructor].  This does expensive operations
 * (like reflection) once and allows it to be cached for subsequent calls.
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class ConstructorConstructor
{
    /**
     * @var InstanceCreator[]
     */
    private $instanceCreators;

    /**
     * Constructor
     *
     * @param InstanceCreator[] $instanceCreators
     */
    public function __construct(array $instanceCreators = [])
    {
        $this->instanceCreators = $instanceCreators;
    }

    /**
     * Get the correct [@see ObjectConstructor] to use
     *
     * @param PhpType $type
     * @return ObjectConstructor
     */
    public function get(PhpType $type): ObjectConstructor
    {
        $class = $type->getClass();
        if (array_key_exists($class, $this->instanceCreators)) {
            return new CreateFromInstanceCreator($this->instanceCreators[$class], $type);
        }

        try {
            // attempt to instantiate a new class without any arguments
            new $class();

            return new CreateWithoutArguments($class);
        } catch (Throwable $throwable) {
            return new CreateFromReflectionClass(new ReflectionClass($class));
        }
    }
}
