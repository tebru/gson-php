<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal\AccessorStrategy;

use Tebru\Gson\Internal\GetterStrategy;

/**
 * Class GetByMethod
 *
 * Get data from an object using a public method
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class GetByMethod implements GetterStrategy
{
    /**
     * The name of the method
     *
     * @var string
     */
    private $methodName;

    /**
     * Constructor
     *
     * @param string $methodName
     */
    public function __construct(string $methodName)
    {
        $this->methodName = $methodName;
    }

    /**
     * Get value from object by method name
     *
     * @param object $object
     * @return mixed
     */
    public function get($object)
    {
        return $object->{$this->methodName}();
    }
}
