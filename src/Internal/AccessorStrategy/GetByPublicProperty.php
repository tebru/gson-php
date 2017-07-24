<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal\AccessorStrategy;

use Tebru\Gson\Internal\GetterStrategy;

/**
 * Class GetByPublicProperty
 *
 * Get data from an object by public property
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class GetByPublicProperty implements GetterStrategy
{
    /**
     * The name of the property
     *
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
     * Get value from object by public property
     *
     * @param object $object
     * @return mixed
     */
    public function get($object)
    {
        return $object->{$this->propertyName};
    }
}
