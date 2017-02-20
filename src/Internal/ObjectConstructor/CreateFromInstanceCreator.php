<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal\ObjectConstructor;

use Tebru\Gson\InstanceCreator;
use Tebru\Gson\Internal\ObjectConstructor;
use Tebru\Gson\PhpType;

/**
 * Class CreateFromInstanceCreator
 *
 * Instantiate a class using a custom [@see InstanceCreator]
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class CreateFromInstanceCreator implements ObjectConstructor
{
    /**
     * User class that instantiates a class in a custom way
     *
     * @var InstanceCreator
     */
    private $instanceCreator;

    /**
     * Php Type instance
     *
     * @var PhpType
     */
    private $type;

    /**
     * Constructor
     *
     * @param InstanceCreator $instanceCreator
     * @param PhpType $type
     */
    public function __construct(InstanceCreator $instanceCreator, PhpType $type)
    {
        $this->instanceCreator = $instanceCreator;
        $this->type = $type;
    }

    /**
     * Returns the instantiated object
     *
     * @return object
     */
    public function construct()
    {
        return $this->instanceCreator->createInstance($this->type);
    }
}
