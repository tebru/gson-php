<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal;

use ReflectionProperty;
use Tebru\AnnotationReader\AnnotationCollection;
use Tebru\Gson\Annotation\Exclude;
use Tebru\Gson\Annotation\Expose;
use Tebru\Gson\Annotation\Since;
use Tebru\Gson\Annotation\Until;
use Tebru\Gson\Exclusion\ClassDeserializationExclusionStrategy;
use Tebru\Gson\Exclusion\ClassSerializationExclusionStrategy;
use Tebru\Gson\Exclusion\DeserializationExclusionData;
use Tebru\Gson\Exclusion\DeserializationExclusionDataAware;
use Tebru\Gson\Exclusion\ExclusionStrategy;
use Tebru\Gson\Exclusion\PropertyDeserializationExclusionStrategy;
use Tebru\Gson\Exclusion\PropertySerializationExclusionStrategy;
use Tebru\Gson\Exclusion\SerializationExclusionData;
use Tebru\Gson\Exclusion\SerializationExclusionDataAware;
use Tebru\Gson\Internal\Data\Property;

/**
 * Class Excluder
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class Excluder
{
    /**
     * Version, if set, will be used with [@see Since] and [@see Until] annotations
     *
     * @var string
     */
    private $version;

    /**
     * Which modifiers are excluded
     *
     * By default only static properties are excluded
     *
     * @var int
     */
    private $excludedModifiers = ReflectionProperty::IS_STATIC;

    /**
     * If this is true, properties will need to explicitly have an [@see Expose] annotation
     * to be serialized or deserialized
     *
     * @var bool
     */
    private $requireExpose = false;

    /**
     * Class Exclusion strategies during serialization
     *
     * @var ClassSerializationExclusionStrategy[]
     */
    private $classSerializationStrategies = [];

    /**
     * Property Exclusion strategies during serialization
     *
     * @var PropertySerializationExclusionStrategy[]
     */
    private $propertySerializationStrategies = [];

    /**
     * Class Exclusion strategies during deserialization
     *
     * @var ClassDeserializationExclusionStrategy[]
     */
    private $classDeserializationStrategies = [];

    /**
     * Property Exclusion strategies during deserialization
     *
     * @var PropertyDeserializationExclusionStrategy[]
     */
    private $propertyDeserializationStrategies = [];

    /**
     * Exclusion strategies that can be cached
     *
     * @var ExclusionStrategy[]
     */
    private $cachedStrategies = [];


    /**
     * Set the version to test against
     *
     * @param string $version
     * @return Excluder
     */
    public function setVersion(?string $version): Excluder
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Set an integer representing the property modifiers that should be excluded
     *
     * @param int $modifiers
     * @return Excluder
     */
    public function setExcludedModifiers(int $modifiers): Excluder
    {
        $this->excludedModifiers = $modifiers;

        return $this;
    }

    /**
     * Require the [@see Expose] annotation to serialize properties
     *
     * @param bool $requireExpose
     * @return Excluder
     */
    public function setRequireExpose(bool $requireExpose): Excluder
    {
        $this->requireExpose = $requireExpose;

        return $this;
    }

    /**
     * Add an exclusion strategy
     *
     * @param ExclusionStrategy $strategy
     * @return void
     */
    public function addExclusionStrategy(ExclusionStrategy $strategy): void
    {
        if ($strategy instanceof ClassSerializationExclusionStrategy) {
            $this->classSerializationStrategies[] = $strategy;
        }

        if ($strategy instanceof PropertySerializationExclusionStrategy) {
            $this->propertySerializationStrategies[] = $strategy;
        }

        if ($strategy instanceof ClassDeserializationExclusionStrategy) {
            $this->classDeserializationStrategies[] = $strategy;
        }

        if ($strategy instanceof PropertyDeserializationExclusionStrategy) {
            $this->propertyDeserializationStrategies[] = $strategy;
        }
    }

    /**
     * Add an exclusion strategy that can be cached
     *
     * @param ExclusionStrategy $strategy
     */
    public function addCachedExclusionStrategy(ExclusionStrategy $strategy): void
    {
        $this->cachedStrategies[] = $strategy;
    }

    /**
     * Compile time exclusion checking of classes to determine if we should exclude during serialization
     *
     * @param DefaultClassMetadata $classMetadata
     * @return bool
     */
    public function excludeClassSerialize(DefaultClassMetadata $classMetadata): bool
    {
        foreach ($this->cachedStrategies as $strategy) {
            if ($strategy instanceof ClassSerializationExclusionStrategy && $strategy->skipSerializingClass($classMetadata)) {
                return true;
            }
        }

        return $this->excludeClass($classMetadata, true);
    }

    /**
     * Compile time exclusion checking of classes to determine if we should exclude during deserialization
     *
     * @param DefaultClassMetadata $classMetadata
     * @return bool
     */
    public function excludeClassDeserialize(DefaultClassMetadata $classMetadata): bool
    {
        foreach ($this->cachedStrategies as $strategy) {
            if ($strategy instanceof ClassDeserializationExclusionStrategy && $strategy->skipDeserializingClass($classMetadata)) {
                return true;
            }
        }

        return $this->excludeClass($classMetadata, false);
    }

    /**
     * Add [@see SerializationExclusionData] to class exclusion strategies
     *
     * @param SerializationExclusionData $exclusionData
     */
    public function applyClassSerializationExclusionData(SerializationExclusionData $exclusionData): void
    {
        foreach ($this->classSerializationStrategies as $exclusionStrategy) {
            if ($exclusionStrategy instanceof SerializationExclusionDataAware) {
                $exclusionStrategy->setSerializationExclusionData($exclusionData);
            }
        }
    }

    /**
     * Add [@see SerializationExclusionData] to property exclusion strategies
     *
     * @param SerializationExclusionData $exclusionData
     */
    public function applyPropertySerializationExclusionData(SerializationExclusionData $exclusionData): void
    {
        foreach ($this->propertySerializationStrategies as $exclusionStrategy) {
            if ($exclusionStrategy instanceof SerializationExclusionDataAware) {
                $exclusionStrategy->setSerializationExclusionData($exclusionData);
            }
        }
    }

    /**
     * Add [@see SerializationExclusionData] to class deserialization strategies
     *
     * @param DeserializationExclusionData $exclusionData
     */
    public function applyClassDeserializationExclusionData(DeserializationExclusionData $exclusionData): void
    {
        foreach ($this->classDeserializationStrategies as $exclusionStrategy) {
            if ($exclusionStrategy instanceof DeserializationExclusionDataAware) {
                $exclusionStrategy->setDeserializationExclusionData($exclusionData);
            }
        }
    }

    /**
     * Add [@see SerializationExclusionData] to property deserialization strategies
     *
     * @param DeserializationExclusionData $exclusionData
     */
    public function applyPropertyDeserializationExclusionData(DeserializationExclusionData $exclusionData): void
    {
        foreach ($this->propertyDeserializationStrategies as $exclusionStrategy) {
            if ($exclusionStrategy instanceof DeserializationExclusionDataAware) {
                $exclusionStrategy->setDeserializationExclusionData($exclusionData);
            }
        }
    }

    /**
     * Runtime exclusion checking of classes by strategy during serialization
     *
     * Uses user-defined strategies
     *
     * @param DefaultClassMetadata $classMetadata
     * @return bool
     */
    public function excludeClassBySerializationStrategy(DefaultClassMetadata $classMetadata): bool
    {
        foreach ($this->classSerializationStrategies as $exclusionStrategy) {
            if ($exclusionStrategy->skipSerializingClass($classMetadata)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Runtime exclusion checking of classes by strategy during deserialization
     *
     * Uses user-defined strategies
     *
     * @param DefaultClassMetadata $classMetadata
     * @return bool
     */
    public function excludeClassByDeserializationStrategy(DefaultClassMetadata $classMetadata): bool
    {
        foreach ($this->classDeserializationStrategies as $exclusionStrategy) {
            if ($exclusionStrategy->skipDeserializingClass($classMetadata)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Compile time exclusion checking of properties
     *
     * @param Property $property
     * @return bool
     */
    public function excludePropertySerialize(Property $property): bool
    {
        // exclude the property if the property modifiers are found in the excluded modifiers
        if (0 !== ($this->excludedModifiers & $property->modifiers)) {
            return true;
        }

        foreach ($this->cachedStrategies as $strategy) {
            if ($strategy instanceof PropertySerializationExclusionStrategy && $strategy->skipSerializingProperty($property)) {
                return true;
            }
        }

        return $this->excludeProperty($property, true);
    }

    /**
     * Compile time exclusion checking of properties
     *
     * @param Property $property
     * @return bool
     */
    public function excludePropertyDeserialize(Property $property): bool
    {
        // exclude the property if the property modifiers are found in the excluded modifiers
        if (0 !== ($this->excludedModifiers & $property->modifiers)) {
            return true;
        }

        foreach ($this->cachedStrategies as $strategy) {
            if ($strategy instanceof PropertyDeserializationExclusionStrategy && $strategy->skipDeserializingProperty($property)) {
                return true;
            }
        }

        return $this->excludeProperty($property, false);
    }

    /**
     * Runtime exclusion checking of properties by strategy during serialization
     *
     * Uses user-defined strategies
     *
     * @param Property $property
     * @return bool
     */
    public function excludePropertyBySerializationStrategy(Property $property): bool
    {
        foreach ($this->propertySerializationStrategies as $exclusionStrategy) {
            if ($exclusionStrategy->skipSerializingProperty($property)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Runtime exclusion checking of properties by strategy during deserialization
     *
     * Uses user-defined strategies
     *
     * @param Property $property
     * @return bool
     */
    public function excludePropertyByDeserializationStrategy(Property $property): bool
    {
        foreach ($this->propertyDeserializationStrategies as $exclusionStrategy) {
            if ($exclusionStrategy->skipDeserializingProperty($property)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if class serialization strategies exist
     *
     * @return bool
     */
    public function hasClassSerializationStrategies(): bool
    {
        return $this->classSerializationStrategies !== [];
    }

    /**
     * Returns true if property serialization strategies exist
     *
     * @return bool
     */
    public function hasPropertySerializationStrategies(): bool
    {
        return $this->propertySerializationStrategies !== [];
    }

    /**
     * Returns true if class deserialization strategies exist
     *
     * @return bool
     */
    public function hasClassDeserializationStrategies(): bool
    {
        return $this->classDeserializationStrategies !== [];
    }

    /**
     * Returns true if property deserialization strategies exist
     *
     * @return bool
     */
    public function hasPropertyDeserializationStrategies(): bool
    {
        return $this->propertyDeserializationStrategies !== [];
    }

    /**
     * Check if we should exclude an entire class. Returns true if the class has an [@Exclude] annotation
     * unless one of the properties has an [@Expose] annotation
     *
     * @param DefaultClassMetadata $classMetadata
     * @param bool $serialize
     * @return bool
     */
    private function excludeClass(DefaultClassMetadata $classMetadata, bool $serialize): bool
    {
        $annotations = $classMetadata->annotations;

        // exclude if version doesn't match
        if (!$this->validVersion($annotations)) {
            return true;
        }

        // exclude if requireExpose is set and class doesn't have an expose annotation
        $expose = $annotations->get(Expose::class);
        if ($this->requireExpose && ($expose !== null && !$expose->shouldExpose($serialize))) {
            return true;
        }

        // don't exclude if exclude annotation doesn't exist or only exists for the other direction
        $exclude = $annotations->get(Exclude::class);
        if ($exclude === null || !$exclude->shouldExclude($serialize)) {
            return false;
        }

        // don't exclude if the annotation exists, but a property is exposed
        foreach ($classMetadata->properties->toArray() as $property) {
            $expose = $property->annotations->get(Expose::class);
            if ($expose !== null && $expose->shouldExpose($serialize)) {
                return false;
            }
        }

        // exclude if an annotation is set and no properties are exposed
        return true;
    }

    /**
     * Checks various annotations to see if the property should be excluded
     *
     * - [@see Since] / [@see Until]
     * - [@see Exclude]
     * - [@see Expose] (if requireExpose is set)
     *
     * @param Property $property
     * @param bool $serialize
     * @return bool
     *
     */
    private function excludeProperty(Property $property, bool $serialize): bool
    {
        $annotations = $property->getAnnotations();
        if (!$this->validVersion($annotations)) {
            return true;
        }

        // exclude from annotation
        $exclude = $annotations->get(Exclude::class);
        if (null !== $exclude && $exclude->shouldExclude($serialize)) {
            return true;
        }

        $classExclude = $property->classMetadata->annotations->get(Exclude::class);

        // if we need an expose annotation
        if ($this->requireExpose || ($classExclude !== null && $classExclude->shouldExclude($serialize))) {
            $expose = $annotations->get(Expose::class);
            if (null === $expose || !$expose->shouldExpose($serialize)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if the set version is valid for [@see Since] and [@see Until] annotations
     *
     * @param AnnotationCollection $annotations
     * @return bool
     */
    private function validVersion(AnnotationCollection $annotations): bool
    {
        return !$this->shouldSkipSince($annotations) && !$this->shouldSkipUntil($annotations);
    }

    /**
     * Returns true if we should skip based on the [@see Since] annotation
     *
     * @param AnnotationCollection $annotations
     * @return bool
     */
    private function shouldSkipSince(AnnotationCollection $annotations): bool
    {
        $sinceAnnotation = $annotations->get(Since::class);

        return
            null !== $sinceAnnotation
            && null !== $this->version
            && version_compare($this->version, $sinceAnnotation->getValue(), '<');
    }

    /**
     * Returns true if we should skip based on the [@see Until] annotation
     *
     * @param AnnotationCollection $annotations
     * @return bool
     */
    private function shouldSkipUntil(AnnotationCollection $annotations): bool
    {
        $untilAnnotation = $annotations->get(Until::class);

        return
            null !== $untilAnnotation
            && null !== $this->version
            && version_compare($this->version, $untilAnnotation->getValue(), '>=');
    }
}
