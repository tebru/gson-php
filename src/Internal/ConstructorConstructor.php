<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal;

use Tebru\Gson\InstanceCreator;
use Tebru\Gson\Internal\ObjectConstructor\CreateFromInstanceCreator;
use Tebru\Gson\Internal\ObjectConstructor\CreateFromReflectionClass;
use Tebru\Gson\Internal\ObjectConstructor\CreateWithoutArguments;
use Tebru\PhpType\TypeToken;
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
     * An array of [@see InstanceCreator] objects that can be used
     * for custom instantiation of a class
     *
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
     * @param TypeToken $type
     * @return ObjectConstructor
     */
    public function get(TypeToken $type): ObjectConstructor
    {
        $class = $type->getRawType();
        foreach ($this->instanceCreators as $instanceCreatorClass => $creator) {
            if ($type->isA($instanceCreatorClass)) {
                return new CreateFromInstanceCreator($creator, $type);
            }
        }

        try {
            // attempt to instantiate a new class without any arguments
            new $class();

            return new CreateWithoutArguments($class);
        } /** @noinspection BadExceptionsProcessingInspection */ catch (Throwable $throwable) {
            return new CreateFromReflectionClass($class);
        }
    }
}
