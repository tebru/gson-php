<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal;

use ReflectionClass;
use Tebru\Collection\MapInterface;
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
     * @var MapInterface
     */
    private $instanceCreators;

    /**
     * Constructor
     *
     * @param MapInterface $instanceCreators
     */
    public function __construct(MapInterface $instanceCreators)
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
        if ($this->instanceCreators->containsKey($class)) {
            return new CreateFromInstanceCreator($this->instanceCreators->get($class), $type);
        }

        try {
            // attempt to instantiate a new class without any arguments
            new $class();

            return new CreateWithoutArguments($type);
        } catch (Throwable $throwable) {
            return new CreateFromReflectionClass(new ReflectionClass($class));
        }
    }
}
