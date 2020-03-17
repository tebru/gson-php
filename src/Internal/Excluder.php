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
use Tebru\Gson\Annotation\ExclusionStrategy as ExclusionStrategyAnnotation;
use Tebru\Gson\Annotation\Expose;
use Tebru\Gson\Annotation\Since;
use Tebru\Gson\Annotation\Until;
use Tebru\Gson\Exclusion\ExclusionStrategy;
use Tebru\Gson\Internal\Data\Property;
use Tebru\PhpType\TypeToken;

/**
 * Class Excluder
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class Excluder
{
    /**
     * @var ConstructorConstructor
     */
    private $constructorConstructor;

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
     * Exclusion strategies that can be cached
     *
     * @var ExclusionStrategy[]
     */
    private $exclusionStrategies = [];

    /**
     * A cache of the created strategies from annotations
     *
     * @var ExclusionStrategy[][]
     */
    private $runtimeExclusions = [];

    /**
     * Constructor
     *
     * @param ConstructorConstructor $constructorConstructor
     */
    public function __construct(ConstructorConstructor $constructorConstructor)
    {
        $this->constructorConstructor = $constructorConstructor;
    }

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
     * Add an exclusion strategy that is cacheable
     *
     * @param ExclusionStrategy $strategy
     */
    public function addExclusionStrategy(ExclusionStrategy $strategy): void
    {
        $this->exclusionStrategies[] = $strategy;
    }

    /**
     * Compile time exclusion checking of classes to determine if we should exclude during serialization
     *
     * @param DefaultClassMetadata $classMetadata
     * @return bool
     */
    public function excludeClassSerialize(DefaultClassMetadata $classMetadata): bool
    {
        if ($this->skipClassSerializeByStrategy($classMetadata, null, true)) {
            return true;
        }

        foreach ($this->exclusionStrategies as $strategy) {
            if ($strategy->skipSerializingClass($classMetadata)) {
                return true;
            }
        }

        return $this->excludeClass($classMetadata, true);
    }

    /**
     * Skip serializing class by ExclusionStrategy annotation
     *
     * @param DefaultClassMetadata $class
     * @param null $object
     * @param bool $cache
     * @return bool
     */
    public function skipClassSerializeByStrategy(DefaultClassMetadata $class, $object = null, bool $cache = false): bool
    {
        foreach ($this->createStrategies($class->name, $class->annotations) as $strategy) {
            if (($cache && $strategy->cacheResult()) && $strategy->skipSerializingClass($class, $object)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Compile time exclusion checking of classes to determine if we should exclude during deserialization
     *
     * @param DefaultClassMetadata $class
     * @return bool
     */
    public function excludeClassDeserialize(DefaultClassMetadata $class): bool
    {
        if ($this->skipClassDeserializeByStrategy($class, null, null, true)) {
            return true;
        }

        foreach ($this->exclusionStrategies as $strategy) {
            if ($strategy->skipDeserializingClass($class)) {
                return true;
            }
        }

        return $this->excludeClass($class, false);
    }

    /**
     * Skip deserializing class by ExclusionStrategy annotation
     *
     * @param DefaultClassMetadata $class
     * @param null $object
     * @param null $payload
     * @param bool $cache
     * @return bool
     */
    public function skipClassDeserializeByStrategy(DefaultClassMetadata $class, $object = null, $payload = null, bool $cache = false): bool
    {
        foreach ($this->createStrategies($class->name, $class->annotations) as $strategy) {
            if (($cache && $strategy->cacheResult()) && $strategy->skipDeserializingClass($class, $payload, $object)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if runtime strategies exist
     *
     * @param DefaultClassMetadata $class
     * @return bool
     */
    public function hasRuntimeClassStrategies(DefaultClassMetadata $class): bool
    {
        foreach ($this->createStrategies($class->name, $class->annotations) as $strategy) {
            if ($strategy->cacheResult() === false) {
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

        if ($this->skipPropertySerializeByStrategy($property, null, true)) {
            return true;
        }

        foreach ($this->exclusionStrategies as $strategy) {
            if ($strategy->skipSerializingProperty($property)) {
                return true;
            }
        }

        return $this->excludeProperty($property, true);
    }

    /**
     * Skip serializing property by ExclusionStrategy annotation
     *
     * @param Property $property
     * @param null $object
     * @param bool $cache
     * @return bool
     */
    public function skipPropertySerializeByStrategy(Property $property, $object = null, bool $cache = false): bool
    {
        foreach ($this->createStrategies($property->realName, $property->annotations) as $strategy) {
            if (($cache && $strategy->cacheResult()) && $strategy->skipSerializingProperty($property, $object)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if runtime strategies exist
     *
     * @param Property $property
     * @return bool
     */
    public function hasRuntimePropertyStrategies(Property $property): bool
    {
        foreach ($this->createStrategies($property->realName, $property->annotations) as $strategy) {
            if ($strategy->cacheResult() === false) {
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
    public function excludePropertyDeserialize(Property $property): bool
    {
        // exclude the property if the property modifiers are found in the excluded modifiers
        if (0 !== ($this->excludedModifiers & $property->modifiers)) {
            return true;
        }

        if ($this->skipPropertyDeserializeByStrategy($property, null, null, true)) {
            return true;
        }

        foreach ($this->exclusionStrategies as $strategy) {
            if ($strategy->skipDeserializingProperty($property)) {
                return true;
            }
        }

        return $this->excludeProperty($property, false);
    }

    /**
     * Skip deserializing property by ExclusionStrategy annotation
     *
     * @param Property $property
     * @param null $object
     * @param null $payload
     * @param bool $cache
     * @return bool
     */
    public function skipPropertyDeserializeByStrategy(Property $property, $object = null, $payload = null, bool $cache = false): bool
    {
        foreach ($this->createStrategies($property->realName, $property->annotations) as $strategy) {
            if (($cache === $strategy->cacheResult()) && $strategy->skipDeserializingProperty($property, $object, $payload)) {
                return true;
            }
        }

        return false;
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

    /**
     * @param string $key
     * @param AnnotationCollection $annotationCollection
     * @return ExclusionStrategy[]
     */
    private function createStrategies(string $key, AnnotationCollection $annotationCollection): array
    {
        if (!isset($this->runtimeExclusions[$key])) {
            $this->runtimeExclusions[$key] = array_map(function (ExclusionStrategyAnnotation $exclusion) {
                return $this->constructorConstructor->get(TypeToken::create($exclusion->getValue()))->construct();
            }, $annotationCollection->getAll(ExclusionStrategyAnnotation::class) ?? []);
        }

        return $this->runtimeExclusions[$key];
    }
}
