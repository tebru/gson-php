<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson;

use BadMethodCallException;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\ChainCache;
use Doctrine\Common\Cache\FilesystemCache;
use ReflectionProperty;
use Tebru\Gson\Internal\AccessorMethodProvider;
use Tebru\Gson\Internal\AccessorStrategyFactory;
use Tebru\Gson\Internal\ConstructorConstructor;
use Tebru\Gson\Internal\Data\AnnotationCollectionFactory;
use Tebru\Gson\Internal\Data\PropertyCollectionFactory;
use Tebru\Gson\Internal\Data\ReflectionPropertySetFactory;
use Tebru\Gson\Internal\Excluder;
use Tebru\Gson\Internal\Naming\PropertyNamer;
use Tebru\Gson\Internal\Naming\SnakePropertyNamingStrategy;
use Tebru\Gson\Internal\Naming\UpperCaseMethodNamingStrategy;
use Tebru\Gson\Internal\PhpTypeFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\ArrayTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\BooleanTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\CustomWrappedTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\DateTimeTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\ExcluderTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\FloatTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\IntegerTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\JsonElementTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\JsonTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\NullTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\ReflectionTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\StringTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\WildcardTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapterProvider;

/**
 * Class GsonBuilder
 *
 * @author Nate Brunette <n@tebru.net>
 */
class GsonBuilder
{
    /**
     * Array of type adapter factories
     *
     * @var TypeAdapterFactory[]
     */
    private $typeAdapterFactories = [];

    /**
     * @var TypeAdapter[]
     */
    private $typeAdapters = [];

    /**
     * @var InstanceCreator[]
     */
    private $instanceCreators = [];

    /**
     * Strategy for converting property names to serialized names
     *
     * @var PropertyNamingStrategy
     */
    private $propertyNamingStrategy;

    /**
     * Strategy for converting property names to method names
     *
     * @var MethodNamingStrategy
     */
    private $methodNamingStrategy;

    /**
     * The version that should be used with Since/Until annotations
     *
     * @var string
     */
    private $version;

    /**
     * Modifiers from [@see ReflectionProperty] that should be excluded
     *
     * @var int
     */
    private $excludedModifiers = ReflectionProperty::IS_STATIC;

    /**
     * True if the [@see Expose] annotation is required for serialization/deserialization
     *
     * @var bool
     */
    private $requireExpose = false;

    /**
     * An array of [@see ExclusionStrategy] objects
     *
     * @var ExclusionStrategy[]
     */
    private $exclusionStrategies = [];

    /**
     * If we should serialize nulls, defaults to false
     *
     * @var bool
     */
    private $serializeNull = false;

    /**
     * Cache directory, if set this enabled filesystem caching
     *
     * @var string
     */
    private $cacheDir;

    /**
     * Add a custom type adapter
     *
     * @param TypeAdapterFactory $typeAdapterFactory
     * @return GsonBuilder
     */
    public function addTypeAdapterFactory(TypeAdapterFactory $typeAdapterFactory): GsonBuilder
    {
        $this->typeAdapterFactories[] = $typeAdapterFactory;

        return $this;
    }

    /**
     * Add custom handling for a specific type
     *
     * Handler objects may be of types: TypeAdapter, JsonSerializer, or JsonDeserializer
     *
     * @param string $type
     * @param $handler
     * @return GsonBuilder
     * @throws \BadMethodCallException If the handler is not supported
     * @throws \Tebru\Gson\Exception\MalformedTypeException If the type cannot be parsed
     */
    public function registerType(string $type, $handler): GsonBuilder
    {
        if ($handler instanceof TypeAdapter) {
            $phpType = new PhpType($type);
            $this->typeAdapters[$phpType->getUniqueKey()] = $handler;

            return $this;
        }

        if ($handler instanceof JsonSerializer && $handler instanceof JsonDeserializer) {
            $this->typeAdapterFactories[] = new CustomWrappedTypeAdapterFactory(new PhpType($type), $handler, $handler);

            return $this;
        }

        if ($handler instanceof JsonSerializer) {
            $this->typeAdapterFactories[] = new CustomWrappedTypeAdapterFactory(new PhpType($type), $handler);

            return $this;
        }

        if ($handler instanceof JsonDeserializer) {
            $this->typeAdapterFactories[] = new CustomWrappedTypeAdapterFactory(new PhpType($type), null, $handler);

            return $this;
        }

        throw new BadMethodCallException(sprintf('Handler of type "%s" is not supported', get_class($handler)));
    }

    /**
     * Add an [@see InstanceCreator] for a given type
     *
     * @param string $type
     * @param InstanceCreator $instanceCreator
     * @return GsonBuilder
     * @throws \Tebru\Gson\Exception\MalformedTypeException If the type cannot be parsed
     */
    public function addInstanceCreator(string $type, InstanceCreator $instanceCreator): GsonBuilder
    {
        $phpType = new PhpType($type);
        $this->instanceCreators[$phpType->getUniqueKey()] = $instanceCreator;

        return $this;
    }

    /**
     * Set the version to be used with [@see Since] and [@see Until] annotations
     *
     * @param string $version
     * @return GsonBuilder
     */
    public function setVersion(string $version): GsonBuilder
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Set the property modifiers that should be excluded based on [@see \ReflectionProperty]
     *
     * This number is a bitmap, so ReflectionProperty::IS_STATIC will exclude all static properties.
     * Likewise, passing (ReflectionProperty::IS_STATIC | ReflectionProperty::IS_PRIVATE) will exclude
     * all static and private properties.
     *
     * @param int $modifiers
     * @return GsonBuilder
     */
    public function setExcludedModifier(int $modifiers): GsonBuilder
    {
        $this->excludedModifiers = $modifiers;

        return $this;
    }

    /**
     * Require the [@see Expose] annotation to serialize or deserialize property
     *
     * @return GsonBuilder
     */
    public function requireExposeAnnotation(): GsonBuilder
    {
        $this->requireExpose = true;

        return $this;
    }

    /**
     * Add an exclusion strategy that should be used during serialization/deserialization
     *
     * @param ExclusionStrategy $strategy
     * @param bool $serialization
     * @param bool $deserialization
     * @return GsonBuilder
     */
    public function addExclusionStrategy(ExclusionStrategy $strategy, bool $serialization, bool $deserialization): GsonBuilder
    {
        $this->exclusionStrategies[] = [$strategy, $serialization, $deserialization];

        return $this;
    }

    /**
     * Set a custom property naming strategy
     *
     * @param PropertyNamingStrategy $propertyNamingStrategy
     * @return GsonBuilder
     */
    public function setPropertyNamingStrategy(PropertyNamingStrategy $propertyNamingStrategy): GsonBuilder
    {
        $this->propertyNamingStrategy = $propertyNamingStrategy;

        return $this;
    }

    /**
     * Set a custom method naming strategy
     *
     * @param MethodNamingStrategy $methodNamingStrategy
     * @return GsonBuilder
     */
    public function setMethodNamingStrategy(MethodNamingStrategy $methodNamingStrategy): GsonBuilder
    {
        $this->methodNamingStrategy = $methodNamingStrategy;

        return $this;
    }

    /**
     * Set whether we should serialize null
     *
     * @return GsonBuilder
     */
    public function serializeNull(): GsonBuilder
    {
        $this->serializeNull = true;

        return $this;
    }

    /**
     * Setting a cache directory will turn on filesystem caching
     *
     * @codeCoverageIgnore
     * @param string $cacheDir
     * @return GsonBuilder
     */
    public function setCacheDir(string $cacheDir): GsonBuilder
    {
        $this->cacheDir = $cacheDir.'/gson';

        return $this;
    }

    /**
     * Builds a new [@see Gson] object based on configuration set
     *
     * @return Gson
     * @throws \InvalidArgumentException If there was a problem creating the cache
     */
    public function build(): Gson
    {
        $propertyNamingStrategy = $this->propertyNamingStrategy ?? new SnakePropertyNamingStrategy();
        $methodNamingStrategy = $this->methodNamingStrategy ?? new UpperCaseMethodNamingStrategy();

        $doctrineAnnotationCache = null === $this->cacheDir ? new ArrayCache(): new ChainCache([new ArrayCache(), new FilesystemCache($this->cacheDir)]);
        $doctrineAnnotationCache->setNamespace('doctrine_annotation_cache');
        $reader = new CachedReader(new AnnotationReader(), $doctrineAnnotationCache);

        $cache = null === $this->cacheDir ? new ArrayCache() : new ChainCache([new ArrayCache(), new FilesystemCache($this->cacheDir)]);
        $cache->setNamespace('property_collection_cache');

        $annotationCache = null === $this->cacheDir ? new ArrayCache(): new ChainCache([new ArrayCache(), new FilesystemCache($this->cacheDir)]);
        $annotationCache->setNamespace('annotation_cache');

        $typeAdapterCache = null === $this->cacheDir ? new ArrayCache(): new ChainCache([new ArrayCache(), new FilesystemCache($this->cacheDir)]);
        $typeAdapterCache->setNamespace('type_adapter_cache');

        $annotationCollectionFactory = new AnnotationCollectionFactory($reader, $annotationCache);
        $excluder = new Excluder($annotationCollectionFactory);
        $excluder->setVersion($this->version);
        $excluder->setExcludedModifiers($this->excludedModifiers);
        $excluder->setRequireExpose($this->requireExpose);
        foreach ($this->exclusionStrategies as $strategy) {
            $excluder->addExclusionStrategy($strategy[0], $strategy[1], $strategy[2]);
        }

        $propertyCollectionFactory = new PropertyCollectionFactory(
            new ReflectionPropertySetFactory(),
            $annotationCollectionFactory,
            new PropertyNamer($propertyNamingStrategy),
            new AccessorMethodProvider($methodNamingStrategy),
            new AccessorStrategyFactory(),
            new PhpTypeFactory(),
            $excluder,
            $cache
        );
        $typeAdapterProvider = new TypeAdapterProvider(
            $this->getTypeAdapterFactories($propertyCollectionFactory, $excluder, $annotationCollectionFactory),
            $typeAdapterCache
        );

        foreach ($this->typeAdapters as $type => $typeAdapter) {
            $typeAdapterProvider->addTypeAdapter($type, $typeAdapter);
        }

        return new Gson($typeAdapterProvider, $propertyCollectionFactory, $this->serializeNull);
    }

    /**
     * Merges default factories with user provided factories
     *
     * @param PropertyCollectionFactory $propertyCollectionFactory
     * @param Excluder $excluder
     * @param AnnotationCollectionFactory $annotationCollectionFactory
     * @return array|TypeAdapterFactory[]
     */
    private function getTypeAdapterFactories(
        PropertyCollectionFactory $propertyCollectionFactory,
        Excluder $excluder,
        AnnotationCollectionFactory $annotationCollectionFactory
    ): array {
        return array_merge(
            [
                new ExcluderTypeAdapterFactory($excluder),
                new JsonTypeAdapterFactory($annotationCollectionFactory),
            ],
            $this->typeAdapterFactories,
            [
                new StringTypeAdapterFactory(),
                new IntegerTypeAdapterFactory(),
                new FloatTypeAdapterFactory(),
                new BooleanTypeAdapterFactory(),
                new NullTypeAdapterFactory(),
                new DateTimeTypeAdapterFactory(),
                new ArrayTypeAdapterFactory(),
                new JsonElementTypeAdapterFactory(),
                new ReflectionTypeAdapterFactory(new ConstructorConstructor($this->instanceCreators), $propertyCollectionFactory, $excluder),
                new WildcardTypeAdapterFactory(),
            ]
        );
    }
}
