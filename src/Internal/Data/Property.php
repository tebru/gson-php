<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal\Data;

use Tebru\Gson\Internal\GetterStrategy;
use Tebru\Gson\Internal\PhpType;
use Tebru\Gson\Internal\SetterStrategy;

/**
 * Class Property
 *
 * Represents static information about an object property.  Instances of this class may be
 * cached for later use.
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class Property
{
    /**
     * The actual name of the property
     *
     * @var string
     */
    private $realName;

    /**
     * The serialized version of the property name
     *
     * @var string
     */
    private $serializedName;

    /**
     * The property type
     *
     * @var PhpType
     */
    private $type;

    /**
     * The method for getting values from this property
     *
     * @var GetterStrategy
     */
    private $getterStrategy;

    /**
     * The method for setting values to this property
     *
     * @var SetterStrategy
     */
    private $setterStrategy;

    /**
     * Constructor
     *
     * @param string $realName
     * @param string $serializedName
     * @param PhpType $type
     * @param GetterStrategy $getterStrategy
     * @param SetterStrategy $setterStrategy
     */
    public function __construct(
        string $realName,
        string $serializedName,
        PhpType $type,
        GetterStrategy $getterStrategy,
        SetterStrategy $setterStrategy
    ) {
        $this->realName = $realName;
        $this->serializedName = $serializedName;
        $this->type = $type;
        $this->getterStrategy = $getterStrategy;
        $this->setterStrategy = $setterStrategy;
    }

    /**
     * Get the real name of the property
     *
     * @return string
     */
    public function getRealName(): string
    {
        return $this->realName;
    }

    /**
     * Get the serialized name of the property
     *
     * @return string
     */
    public function getSerializedName(): string
    {
        return $this->serializedName;
    }

    /**
     * Get the property type
     *
     * @return PhpType
     */
    public function getType(): PhpType
    {
        return $this->type;
    }

    /**
     * Given an object, get the value at this property
     *
     * @param object $object
     * @return mixed
     */
    public function get($object)
    {
        return $this->getterStrategy->get($object);
    }

    /**
     * Given an object an value, set the value to the object at this property
     *
     * @param object $object
     * @param $value
     */
    public function set($object, $value)
    {
        $this->setterStrategy->set($object, $value);
    }
}
