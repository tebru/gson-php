<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson;

use DateTime;
use Doctrine\Common\Annotations\AnnotationReader;
use InvalidArgumentException;
use LogicException;
use Psr\SimpleCache\CacheInterface;
use ReflectionProperty;
use Symfony\Component\Cache\Simple\ArrayCache;
use Symfony\Component\Cache\Simple\ChainCache;
use Symfony\Component\Cache\Simple\PhpFilesCache;
use Tebru\AnnotationReader\AnnotationReaderAdapter;
use Tebru\Gson\Internal\AccessorMethodProvider;
use Tebru\Gson\Internal\AccessorStrategyFactory;
use Tebru\Gson\Internal\ConstructorConstructor;
use Tebru\Gson\Internal\Data\PropertyCollectionFactory;
use Tebru\Gson\Internal\Data\ReflectionPropertySetFactory;
use Tebru\Gson\Internal\Excluder;
use Tebru\Gson\Internal\MetadataFactory;
use Tebru\Gson\Internal\Naming\DefaultPropertyNamingStrategy;
use Tebru\Gson\Internal\Naming\PropertyNamer;
use Tebru\Gson\Internal\Naming\UpperCaseMethodNamingStrategy;
use Tebru\Gson\Internal\PhpTypeFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\ArrayTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\BooleanTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\CustomWrappedTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\DateTimeTypeAdapterFactory;
use Tebru\Gson\Internal\DiscriminatorDeserializer;
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
use Tebru\PhpType\TypeToken;

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
     * Property naming policy
     *
     * Defaults to converting camel case to snake case
     *
     * @var string
     */
    private $propertyNamingPolicy = PropertyNamingPolicy::LOWER_CASE_WITH_UNDERSCORES;

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
     * A cache interface to be used in place of defaults
     *
     * If this is set, [@see GsonBuilder::$enableCache] will be ignored
     *
     * @var CacheInterface
     */
    private $cache;

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
     * Adds a [@see Discriminator] as a type adapter factory
     *
     * @param string $type
     * @param Discriminator $discriminator
     * @return GsonBuilder
     */
    public function addDiscriminator(string $type, Discriminator $discriminator): GsonBuilder
    {
        $this->typeAdapterFactories[] = new CustomWrappedTypeAdapterFactory(
            new TypeToken($type),
            true,
            null,
            new DiscriminatorDeserializer($discriminator)
        );

        return $this;
    }

    /**
     * Add custom handling for a specific type
     *
     * Handler objects may be of types: TypeAdapter, JsonSerializer, or JsonDeserializer. Passing
     * $strict=true will match the specified type exactly, as opposed to checking anywhere in the
     * inheritance chain.
     *
     * @param string $type
     * @param $handler
     * @param bool $strict
     * @return GsonBuilder
     */
    public function registerType(string $type, $handler, bool $strict = false): GsonBuilder
    {
        if ($handler instanceof TypeAdapter) {
            $this->typeAdapterFactories[] = new WrappedTypeAdapterFactory($handler, new TypeToken($type), $strict);

            return $this;
        }

        if ($handler instanceof JsonSerializer && $handler instanceof JsonDeserializer) {
            $this->typeAdapterFactories[] = new CustomWrappedTypeAdapterFactory(new TypeToken($type), $strict, $handler, $handler);

            return $this;
        }

        if ($handler instanceof JsonSerializer) {
            $this->typeAdapterFactories[] = new CustomWrappedTypeAdapterFactory(new TypeToken($type), $strict, $handler);

            return $this;
        }

        if ($handler instanceof JsonDeserializer) {
            $this->typeAdapterFactories[] = new CustomWrappedTypeAdapterFactory(new TypeToken($type), $strict, null, $handler);

            return $this;
        }

        throw new InvalidArgumentException(\sprintf('Handler of type "%s" is not supported', \get_class($handler)));
    }

    /**
     * Add an [@see InstanceCreator] for a given type
     *
     * @param string $type
     * @param InstanceCreator $instanceCreator
     * @return GsonBuilder
     */
    public function addInstanceCreator(string $type, InstanceCreator $instanceCreator): GsonBuilder
    {
        $phpType = new TypeToken($type);
        $this->instanceCreators[$phpType->getRawType()] = $instanceCreator;

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
     * Set one of [@see PropertyNamingPolicy]
     *
     * @param string $policy
     * @return GsonBuilder
     */
    public function setPropertyNamingPolicy(string $policy): GsonBuilder
    {
        $this->propertyNamingPolicy = $policy;

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
     * Override default cache adapters
     *
     * @param CacheInterface $cache
     * @return GsonBuilder
     */
    public function setCache(CacheInterface $cache): GsonBuilder
    {
        $this->cache = $cache;

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
     * @throws \LogicException
     */
    public function build(): Gson
    {
        if (null === $this->cacheDir && true === $this->enableCache) {
            throw new LogicException('Cannot enable cache without a cache directory');
        }

        $propertyNamingStrategy = $this->propertyNamingStrategy ?? new DefaultPropertyNamingStrategy($this->propertyNamingPolicy);
        $methodNamingStrategy = $this->methodNamingStrategy ?? new UpperCaseMethodNamingStrategy();

        if ($this->cache === null) {
            $this->cache = false === $this->enableCache
                ? new ArrayCache(0, false)
                : new ChainCache([new ArrayCache(0, false), new PhpFilesCache('', 0, $this->cacheDir)]);
        }

        $annotationReader = new AnnotationReaderAdapter(new AnnotationReader(), $this->cache);
        $excluder = new Excluder();
        $excluder->setVersion($this->version);
        $excluder->setExcludedModifiers($this->excludedModifiers);
        $excluder->setRequireExpose($this->requireExpose);
        foreach ($this->exclusionStrategies as $strategy) {
            $excluder->addExclusionStrategy($strategy[0], $strategy[1], $strategy[2]);
        }

        $metadataFactory = new MetadataFactory($annotationReader);
        $propertyCollectionFactory = new PropertyCollectionFactory(
            new ReflectionPropertySetFactory(),
            $annotationReader,
            $metadataFactory,
            new PropertyNamer($propertyNamingStrategy),
            new AccessorMethodProvider($methodNamingStrategy),
            new AccessorStrategyFactory(),
            new PhpTypeFactory(),
            $excluder,
            $this->cache
        );
        $constructorConstructor = new ConstructorConstructor($this->instanceCreators);
        $typeAdapterProvider = new TypeAdapterProvider(
            $this->getTypeAdapterFactories($propertyCollectionFactory, $excluder, $annotationReader, $metadataFactory, $constructorConstructor),
            $constructorConstructor
        );

        return new Gson($typeAdapterProvider, $this->serializeNull);
    }

    /**
     * Merges default factories with user provided factories
     *
     * @param PropertyCollectionFactory $propertyCollectionFactory
     * @param Excluder $excluder
     * @param AnnotationReaderAdapter $annotationReader
     * @param MetadataFactory $metadataFactory
     * @param ConstructorConstructor $constructorConstructor
     * @return array|TypeAdapterFactory[]
     */
    private function getTypeAdapterFactories(
        PropertyCollectionFactory $propertyCollectionFactory,
        Excluder $excluder,
        AnnotationReaderAdapter $annotationReader,
        MetadataFactory $metadataFactory,
        ConstructorConstructor $constructorConstructor
    ): array {
        return \array_merge(
            [
                new ExcluderTypeAdapterFactory($excluder, $metadataFactory),
                new JsonTypeAdapterFactory($annotationReader),
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
