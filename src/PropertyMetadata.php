<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson;

use Tebru\Gson\Internal\Data\AnnotationSet;

/**
 * Class PropertyMetadata
 *
 * Represents a property and its annotations
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class PropertyMetadata
{
    /**
     * The property name
     *
     * @var string
     */
    private $name;

    /**
     * The property's serialized name
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
     * The property modifiers (public, private, etc)
     *
     * @var int
     */
    private $modifiers;

    /**
     * The property declaring class metadata
     *
     * @var ClassMetadata
     */
    private $classMetadata;

    /**
     * The property's annotations
     *
     * @var AnnotationSet
     */
    private $annotations;

    /**
     * If the property is a virtual property
     *
     * @var bool
     */
    private $virtual;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $serializedName
     * @param PhpType $type
     * @param string $modifiers
     * @param ClassMetadata $classMetadata
     * @param AnnotationSet $annotations
     * @param bool $virtual
     */
    public function __construct(
        string $name,
        string $serializedName,
        PhpType $type,
        string $modifiers,
        ClassMetadata $classMetadata,
        AnnotationSet $annotations,
        bool $virtual
    )
    {
        $this->name = $name;
        $this->serializedName = $serializedName;
        $this->type = $type;
        $this->modifiers = $modifiers;
        $this->classMetadata = $classMetadata;
        $this->annotations = $annotations;
        $this->virtual = $virtual;
    }

    /**
     * Get the property name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the property serialized name
     *
     * @return string
     */
    public function getSerializedName(): string
    {
        return $this->serializedName;
    }

    /**
     * Get the full php type object
     *
     * @return PhpType
     */
    public function getType(): PhpType
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
        return (string) $this->type;
    }

    /**
     * Get the property modifiers as a bitmap of [@see \ReflectionProperty] constants
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
        return $this->classMetadata->getName();
    }

    /**
     * Get property annotations
     *
     * @return AnnotationSet
     */
    public function getAnnotations(): AnnotationSet
    {
        return $this->annotations;
    }

    /**
     * Get a single annotation, returns null if the annotation doesn't exist
     *
     * @param string $annotationName
     * @return null|object
     */
    public function getAnnotation(string $annotationName)
    {
        return $this->virtual
            ? $this->annotations->getAnnotation($annotationName, AnnotationSet::TYPE_METHOD)
            : $this->annotations->getAnnotation($annotationName, AnnotationSet::TYPE_PROPERTY);
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
}
