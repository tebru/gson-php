<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal\ObjectConstructor;

use ReflectionClass;
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
     * @var string
     */
    private $className;

    /**
     * Constructor
     *
     * @param string $className
     */
    public function __construct(string $className)
    {
        $this->className = $className;
    }

    /**
     * Returns the instantiated object
     *
     * @return object
     */
    public function construct()
    {
        $reflectionClass = new ReflectionClass($this->className);

        return $reflectionClass->newInstanceWithoutConstructor();
    }
}
