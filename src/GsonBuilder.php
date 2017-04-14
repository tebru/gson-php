<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson;

use DateTime;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\ChainCache;
use Doctrine\Common\Cache\FilesystemCache;
use InvalidArgumentException;
use LogicException;
use ReflectionProperty;
use Tebru\Gson\Internal\AccessorMethodProvider;
use Tebru\Gson\Internal\AccessorStrategyFactory;
use Tebru\Gson\Internal\ConstructorConstructor;
use Tebru\Gson\Internal\Data\AnnotationCollectionFactory;
use Tebru\Gson\Internal\Data\PropertyCollectionFactory;
use Tebru\Gson\Internal\Data\ReflectionPropertySetFactory;
use Tebru\Gson\Internal\Excluder;
use Tebru\Gson\Internal\MetadataFactory;
use Tebru\Gson\Internal\Naming\PropertyNamer;
use Tebru\Gson\Internal\Naming\SnakePropertyNamingStrategy;
use Tebru\Gson\Internal\Naming\UpperCaseMethodNamingStrategy;
use Tebru\Gson\Internal\DefaultPhpType;
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
use Tebru\Gson\Internal\TypeAdapter\Factory\WrappedTypeAdapterFactory;
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
     * Default format for DateTimes
     *
     * @var string
     */
    private $dateTimeFormat = DateTime::ATOM;

    /**
     * True if we should be caching
     *
     * @var bool
     */
    private $enableCache = false;

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
     * @throws \InvalidArgumentException
     * @throws \Tebru\Gson\Exception\MalformedTypeException If the type cannot be parsed
     */
    public function registerType(string $type, $handler): GsonBuilder
    {
        if ($handler instanceof TypeAdapter) {
            $this->typeAdapterFactories[] = new WrappedTypeAdapterFactory($handler, new DefaultPhpType($type));

            return $this;
        }

        if ($handler instanceof JsonSerializer && $handler instanceof JsonDeserializer) {
            $this->typeAdapterFactories[] = new CustomWrappedTypeAdapterFactory(new DefaultPhpType($type), $handler, $handler);

            return $this;
        }

        if ($handler instanceof JsonSerializer) {
            $this->typeAdapterFactories[] = new CustomWrappedTypeAdapterFactory(new DefaultPhpType($type), $handler);

            return $this;
        }

        if ($handler instanceof JsonDeserializer) {
            $this->typeAdapterFactories[] = new CustomWrappedTypeAdapterFactory(new DefaultPhpType($type), null, $handler);

            return $this;
        }

        throw new InvalidArgumentException(sprintf('Handler of type "%s" is not supported', get_class($handler)));
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
        $phpType = new DefaultPhpType($type);
        $this->instanceCreators[$phpType->getType()] = $instanceCreator;

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
     * Set the default datetime format
     *
     * @param string $format
     * @return GsonBuilder
     */
    public function setDateTimeFormat(string $format): GsonBuilder
    {
        $this->dateTimeFormat = $format;

        return $this;
    }

    /**
     * Set whether caching is enabled
     *
     * @param bool $enableCache
     * @return GsonBuilder
     */
    public function enableCache(bool $enableCache): GsonBuilder
    {
        $this->enableCache = $enableCache;

        return $this;
    }

    /**
     * Setting a cache directory will turn on filesystem caching
     *
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
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function build(): Gson
    {
        if (null === $this->cacheDir && true === $this->enableCache) {
            throw new LogicException('Cannot enable cache without a cache directory');
        }

        $propertyNamingStrategy = $this->propertyNamingStrategy ?? new SnakePropertyNamingStrategy();
        $methodNamingStrategy = $this->methodNamingStrategy ?? new UpperCaseMethodNamingStrategy();

        $doctrineAnnotationCache = false === $this->enableCache ? new ArrayCache(): new ChainCache([new ArrayCache(), new FilesystemCache($this->cacheDir)]);
        $doctrineAnnotationCache->setNamespace('doctrine_annotation_cache');
        $reader = new CachedReader(new AnnotationReader(), $doctrineAnnotationCache);

        $cache = false === $this->enableCache ? new ArrayCache() : new ChainCache([new ArrayCache(), new FilesystemCache($this->cacheDir)]);
        $cache->setNamespace('gson');

        $annotationCollectionFactory = new AnnotationCollectionFactory($reader, $cache);
        $excluder = new Excluder();
        $excluder->setVersion($this->version);
        $excluder->setExcludedModifiers($this->excludedModifiers);
        $excluder->setRequireExpose($this->requireExpose);
        foreach ($this->exclusionStrategies as $strategy) {
            $excluder->addExclusionStrategy($strategy[0], $strategy[1], $strategy[2]);
        }

        $metadataFactory = new MetadataFactory($annotationCollectionFactory);
        $propertyCollectionFactory = new PropertyCollectionFactory(
            new ReflectionPropertySetFactory(),
            $annotationCollectionFactory,
            $metadataFactory,
            new PropertyNamer($propertyNamingStrategy),
            new AccessorMethodProvider($methodNamingStrategy),
            new AccessorStrategyFactory(),
            new PhpTypeFactory(),
            $excluder,
            $cache
        );
        $constructorConstructor = new ConstructorConstructor($this->instanceCreators);
        $typeAdapterProvider = new TypeAdapterProvider(
            $this->getTypeAdapterFactories($propertyCollectionFactory, $excluder, $annotationCollectionFactory, $metadataFactory, $constructorConstructor),
            $constructorConstructor
        );

        return new Gson($typeAdapterProvider, $propertyCollectionFactory, $this->serializeNull);
    }

    /**
     * Merges default factories with user provided factories
     *
     * @param PropertyCollectionFactory $propertyCollectionFactory
     * @param Excluder $excluder
     * @param AnnotationCollectionFactory $annotationCollectionFactory
     * @param MetadataFactory $metadataFactory
     * @param ConstructorConstructor $constructorConstructor
     * @return array|TypeAdapterFactory[]
     */
    private function getTypeAdapterFactories(
        PropertyCollectionFactory $propertyCollectionFactory,
        Excluder $excluder,
        AnnotationCollectionFactory $annotationCollectionFactory,
        MetadataFactory $metadataFactory,
        ConstructorConstructor $constructorConstructor
    ): array
    {
        return array_merge(
            [
                new ExcluderTypeAdapterFactory($excluder, $metadataFactory),
                new JsonTypeAdapterFactory($annotationCollectionFactory),
            ],
            $this->typeAdapterFactories,
            [
                new StringTypeAdapterFactory(),
                new IntegerTypeAdapterFactory(),
                new FloatTypeAdapterFactory(),
                new BooleanTypeAdapterFactory(),
                new NullTypeAdapterFactory(),
                new DateTimeTypeAdapterFactory($this->dateTimeFormat),
                new ArrayTypeAdapterFactory(),
                new JsonElementTypeAdapterFactory(),
                new ReflectionTypeAdapterFactory(
                    $constructorConstructor,
                    $propertyCollectionFactory,
                    $metadataFactory,
                    $excluder
                ),
                new WildcardTypeAdapterFactory(),
            ]
        );
    }
}
