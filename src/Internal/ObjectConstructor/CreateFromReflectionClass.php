<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal\ObjectConstructor;

use ReflectionClass;
use Tebru\Gson\InstanceCreator;
use Tebru\Gson\Internal\ObjectConstructor;

/**
 * Class CreateFromReflectionClass
 *
 * Instantiate a new class using reflection.  This is necessary if the class constructor
 * has required arguments, but an [@see InstanceCreator] is not registered.
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class CreateFromReflectionClass implements ObjectConstructor
{
    /**
     * @var ReflectionClass
     */
    private $reflectionClass;

    /**
     * Constructor
     *
     * @param ReflectionClass $reflectionClass
     */
    public function __construct(ReflectionClass $reflectionClass)
    {
        $this->reflectionClass = $reflectionClass;
    }

    /**
     * Returns the instantiated object
     *
     * @return object
     */
    public function construct()
    {
        return $this->reflectionClass->newInstanceWithoutConstructor();
    }
}
