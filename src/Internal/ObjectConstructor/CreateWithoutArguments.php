<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal\ObjectConstructor;

use Tebru\Gson\Internal\ObjectConstructor;

/**
 * Class CreateWithoutArguments
 *
 * Instantiates a class that doesn't have any required constructor arguments
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class CreateWithoutArguments implements ObjectConstructor
{
    /**
     * The name of the class
     *
     * @var string
     */
    private $class;

    /**
     * Constructor
     *
     * @param string $class
     */
    public function __construct(string $class)
    {
        $this->class = $class;
    }

    /**
     * Returns the instantiated object
     *
     * @return object
     */
    public function construct()
    {
        return new $this->class();
    }
}
