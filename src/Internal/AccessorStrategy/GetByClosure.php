<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal\AccessorStrategy;

use Closure;
use Tebru\Gson\Internal\GetterStrategy;

/**
 * Class GetByClosure
 *
 * Get data from an object by binding a closure to the class
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class GetByClosure implements GetterStrategy
{
    /**
     * The name of the property
     *
     * @var string
     */
    private $propertyName;

    /**
     * The name of the class
     *
     * @var string
     */
    private $className;

    /**
     * The cached closure
     *
     * @var Closure
     */
    private $getter;

    /**
     * Constructor
     *
     * @param string $propertyName
     * @param string $className
     */
    public function __construct(string $propertyName, string $className)
    {
        $this->propertyName = $propertyName;
        $this->className = $className;
    }

    /**
     * Get object value by binding a closure to the class
     *
     * @param object $object
     * @return mixed
     */
    public function get($object)
    {
        if (null === $this->getter) {
            $this->getter = Closure::bind(function ($object, string $propertyName) {
                return $object->{$propertyName};
            }, null, $this->className);
        }

        $getter = $this->getter;

        return $getter($object, $this->propertyName);
    }
}
