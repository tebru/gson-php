<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal\AccessorStrategy;

use Tebru\Gson\Internal\SetterStrategy;

/**
 * Class SetByPublicProperty
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class SetByPublicProperty implements SetterStrategy
{
    /**
     * @var string
     */
    private $propertyName;

    /**
     * Constructor
     *
     * @param string $propertyName
     */
    public function __construct(string $propertyName)
    {
        $this->propertyName = $propertyName;
    }

    /**
     * Set value to object by method name
     *
     * @param object $object
     * @param mixed $value
     * @return void
     */
    public function set($object, $value): void
    {
        $object->{$this->propertyName} = $value;
    }
}
