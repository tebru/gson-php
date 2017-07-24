<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal\ObjectConstructor;

use Tebru\Gson\InstanceCreator;
use Tebru\Gson\Internal\ObjectConstructor;
use Tebru\PhpType\TypeToken;

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
     * @var TypeToken
     */
    private $type;

    /**
     * Constructor
     *
     * @param InstanceCreator $instanceCreator
     * @param TypeToken $type
     */
    public function __construct(InstanceCreator $instanceCreator, TypeToken $type)
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
