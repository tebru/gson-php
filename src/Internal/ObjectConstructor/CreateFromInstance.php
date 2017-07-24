<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal\ObjectConstructor;

use Tebru\Gson\Internal\ObjectConstructor;

/**
 * Class CreateFromInstance
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class CreateFromInstance implements ObjectConstructor
{
    /**
     * The already instantiated object
     *
     * @var object
     */
    private $object;

    /**
     * Constructor
     *
     * @param object $object
     */
    public function __construct($object)
    {
        $this->object = $object;
    }

    /**
     * Returns the instantiated object
     *
     * @return object
     */
    public function construct()
    {
        return $this->object;
    }
}
