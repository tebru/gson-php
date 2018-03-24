<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal\Data;

use Tebru\AnnotationReader\AnnotationCollection;
use Tebru\Gson\Internal\GetterStrategy;
use Tebru\Gson\Internal\SetterStrategy;
use Tebru\PhpType\TypeToken;

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
     * @var TypeToken
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
     * A set of annotations
     *
     * @var AnnotationCollection
     */
    private $annotations;

    /**
     * An integer that represents what modifiers are associated with the property
     *
     * These constants are defined in [@see \ReflectionProperty]
     *
     * @var int
     */
    private $modifiers;

    /**
     * True if the property should be skipped during serialization
     *
     * @var bool
     */
    private $skipSerialize = false;

    /**
     * True if the property should be skipped during deserialization
     *
     * @var bool
     */
    private $skipDeserialize = false;

    /**
     * If the property is a virtual property
     * @var bool
     */
    private $virtual;

    /**
     * Constructor
     *
     * @param string $realName
     * @param string $serializedName
     * @param TypeToken $type
     * @param GetterStrategy $getterStrategy
     * @param SetterStrategy $setterStrategy
     * @param AnnotationCollection $annotations
     * @param int $modifiers
     * @param bool $virtual
     */
    public function __construct(
        string $realName,
        string $serializedName,
        TypeToken $type,
        GetterStrategy $getterStrategy,
        SetterStrategy $setterStrategy,
        AnnotationCollection $annotations,
        int $modifiers,
        bool $virtual
    ) {
        $this->realName = $realName;
        $this->serializedName = $serializedName;
        $this->type = $type;
        $this->getterStrategy = $getterStrategy;
        $this->setterStrategy = $setterStrategy;
        $this->annotations = $annotations;
        $this->modifiers = $modifiers;
        $this->virtual = $virtual;
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
     * @return TypeToken
     */
    public function getType(): TypeToken
    {
        return $this->type;
    }

    /**
     * Return the collection of annotations
     *
     * @return AnnotationCollection
     */
    public function getAnnotations(): AnnotationCollection
    {
        return $this->annotations;
    }

    /**
     * The property modifiers
     *
     * @return int
     */
    public function getModifiers(): int
    {
        return $this->modifiers;
    }

    /**
     * Returns true if the property is virtual
     *
     * @return bool
     */
    public function isVirtual(): bool
    {
        return $this->virtual;
    }

    /**
     * Returns should if we should skip during serialization
     *
     * @return bool
     */
    public function skipSerialize(): bool
    {
        return $this->skipSerialize;
    }

    /**
     * Set whether we should skip during serialization
     *
     * @param bool $skipSerialize
     */
    public function setSkipSerialize(bool $skipSerialize): void
    {
        $this->skipSerialize = $skipSerialize;
    }

    /**
     * Returns should if we should skip during deserialization
     *
     * @return bool
     */
    public function skipDeserialize(): bool
    {
        return $this->skipDeserialize;
    }

    /**
     * Set whether we should skip during deserialization
     *
     * @param bool $skipDeserialize
     */
    public function setSkipDeserialize(bool $skipDeserialize): void
    {
        $this->skipDeserialize = $skipDeserialize;
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
     * Given an object and value, set the value to the object at this property
     *
     * @param object $object
     * @param mixed $value
     */
    public function set($object, $value): void
    {
        $this->setterStrategy->set($object, $value);
    }
}
