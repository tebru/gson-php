<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal\Data;

use Tebru\AnnotationReader\AbstractAnnotation;
use Tebru\AnnotationReader\AnnotationCollection;
use Tebru\Gson\ClassMetadata;
use Tebru\Gson\Internal\DefaultClassMetadata;
use Tebru\Gson\Internal\GetterStrategy;
use Tebru\Gson\Internal\SetterStrategy;
use Tebru\Gson\PropertyMetadata;
use Tebru\PhpType\TypeToken;

/**
 * Class Property
 *
 * Represents static information about an object property.  Instances of this class may be
 * cached for later use.
 * 
 * This class contains public properties to improve performance.
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class Property implements PropertyMetadata
{
    /**
     * The actual name of the property
     *
     * @var string
     */
    public $realName;

    /**
     * The serialized version of the property name
     *
     * @var string
     */
    public $serializedName;

    /**
     * The property type
     *
     * @var TypeToken
     */
    public $type;

    /**
     * If the property is a scalar type
     *
     * @var bool
     */
    public $isScalar;

    /**
     * The method for getting values from this property
     *
     * @var GetterStrategy
     */
    public $getterStrategy;

    /**
     * The method for setting values to this property
     *
     * @var SetterStrategy
     */
    public $setterStrategy;

    /**
     * A set of annotations
     *
     * @var AnnotationCollection
     */
    public $annotations;

    /**
     * An integer that represents what modifiers are associated with the property
     *
     * These constants are defined in [@see \ReflectionProperty]
     *
     * @var int
     */
    public $modifiers;

    /**
     * The property's class metadata
     *
     * @var DefaultClassMetadata
     */
    public $classMetadata;

    /**
     * True if the property should be skipped during serialization
     *
     * @var bool
     */
    public $skipSerialize = false;

    /**
     * True if the property should be skipped during deserialization
     *
     * @var bool
     */
    public $skipDeserialize = false;

    /**
     * If the property is a virtual property
     * @var bool
     */
    public $virtual;

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
     * @param ClassMetadata $classMetadata
     */
    public function __construct(
        string $realName,
        string $serializedName,
        TypeToken $type,
        GetterStrategy $getterStrategy,
        SetterStrategy $setterStrategy,
        AnnotationCollection $annotations,
        int $modifiers,
        bool $virtual,
        ClassMetadata $classMetadata
    ) {
        $this->realName = $realName;
        $this->serializedName = $serializedName;
        $this->type = $type;
        $this->isScalar = $type->isScalar() && $type->genericTypes === [];
        $this->getterStrategy = $getterStrategy;
        $this->setterStrategy = $setterStrategy;
        $this->annotations = $annotations;
        $this->modifiers = $modifiers;
        $this->virtual = $virtual;
        $this->classMetadata = $classMetadata;
    }

    /**
     * Get the real name of the property
     *
     * @return string
     */
    public function getName(): string
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
     * Get the property type as a string
     *
     * @return string
     */
    public function getTypeName(): string
    {
        return (string)$this->type;
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
     * Get full declaring class metadata
     *
     * @return ClassMetadata
     */
    public function getDeclaringClassMetadata(): ClassMetadata
    {
        return $this->classMetadata;
    }

    /**
     * Get the declaring class name
     *
     * @return string
     */
    public function getDeclaringClassName(): string
    {
        return $this->classMetadata->name;
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
     * Get a single annotation, returns null if the annotation doesn't exist
     *
     * @param string $annotationName
     * @return null|AbstractAnnotation
     */
    public function getAnnotation(string $annotationName): ?AbstractAnnotation
    {
        return $this->annotations->get($annotationName);
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
     * @return PropertyMetadata
     */
    public function setSkipSerialize(bool $skipSerialize): PropertyMetadata
    {
        $this->skipSerialize = $skipSerialize;

        return $this;
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
     * @return PropertyMetadata
     */
    public function setSkipDeserialize(bool $skipDeserialize): PropertyMetadata
    {
        $this->skipDeserialize = $skipDeserialize;

        return $this;
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
