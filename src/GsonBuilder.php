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
use Tebru\AnnotationReader\AnnotationReaderAdapter;
use Tebru\Gson\Annotation\ExclusionCheck;
use Tebru\Gson\Context\ReaderContext;
use Tebru\Gson\Context\WriterContext;
use Tebru\Gson\Exclusion\DeserializationExclusionDataAware;
use Tebru\Gson\Exclusion\ExclusionStrategy;
use Tebru\Gson\Exclusion\SerializationExclusionDataAware;
use Tebru\Gson\Internal\AccessorMethodProvider;
use Tebru\Gson\Internal\AccessorStrategyFactory;
use Tebru\Gson\Internal\ConstructorConstructor;
use Tebru\Gson\Internal\Data\ClassMetadataFactory;
use Tebru\Gson\Internal\Data\ReflectionPropertySetFactory;
use Tebru\Gson\Internal\CacheProvider;
use Tebru\Gson\Internal\DiscriminatorDeserializer;
use Tebru\Gson\Internal\Excluder;
use Tebru\Gson\Internal\Naming\DefaultPropertyNamingStrategy;
use Tebru\Gson\Internal\Naming\PropertyNamer;
use Tebru\Gson\Internal\Naming\UpperCaseMethodNamingStrategy;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\Internal\TypeTokenFactory;
use Tebru\Gson\TypeAdapter\BooleanTypeAdapter;
use Tebru\Gson\TypeAdapter\Factory\ArrayTypeAdapterFactory;
use Tebru\Gson\TypeAdapter\Factory\CustomWrappedTypeAdapterFactory;
use Tebru\Gson\TypeAdapter\Factory\DateTimeTypeAdapterFactory;
use Tebru\Gson\TypeAdapter\Factory\ReflectionTypeAdapterFactory;
use Tebru\Gson\TypeAdapter\Factory\WildcardTypeAdapterFactory;
use Tebru\Gson\TypeAdapter\Factory\WrappedTypeAdapterFactory;
use Tebru\Gson\TypeAdapter\FloatTypeAdapter;
use Tebru\Gson\TypeAdapter\IntegerTypeAdapter;
use Tebru\Gson\TypeAdapter\NullTypeAdapter;
use Tebru\Gson\TypeAdapter\StringTypeAdapter;
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
     * List of visitors to manipulate class metadata when loaded
     *
     * @var ClassMetadataVisitor[]
     */
    private $classMetadataVisitors = [];

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
     * True if the [@see ExclusionCheck] annotation is required to use non-cached exclusion strategies
     *
     * @var bool
     */
    private $requireExclusionCheck = false;

    /**
     * An array of [@see ExclusionStrategy] objects
     *
     * @var ExclusionStrategy[]
     */
    private $exclusionStrategies = [];

    /**
     * An array of Cacheable [@see ExclusionStrategy] objects
     *
     * @var ExclusionStrategy[]
     */
    private $cachedExclusionStrategies = [];

    /**
     * @var ReaderContext
     */
    private $readerContext;

    /**
     * @var WriterContext
     */
    private $writerContext;

    /**
     * @var bool|null
     */
    private $enableScalarAdapters;

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
            TypeToken::create($type),
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
            $this->typeAdapterFactories[] = new WrappedTypeAdapterFactory($handler, TypeToken::create($type), $strict);

            return $this;
        }

        if ($handler instanceof JsonSerializer && $handler instanceof JsonDeserializer) {
            $this->typeAdapterFactories[] = new CustomWrappedTypeAdapterFactory(TypeToken::create($type), $strict, $handler, $handler);

            return $this;
        }

        if ($handler instanceof JsonSerializer) {
            $this->typeAdapterFactories[] = new CustomWrappedTypeAdapterFactory(TypeToken::create($type), $strict, $handler);

            return $this;
        }

        if ($handler instanceof JsonDeserializer) {
            $this->typeAdapterFactories[] = new CustomWrappedTypeAdapterFactory(TypeToken::create($type), $strict, null, $handler);

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
     */
    public function addInstanceCreator(string $type, InstanceCreator $instanceCreator): GsonBuilder
    {
        $phpType = TypeToken::create($type);
        $this->instanceCreators[$phpType->rawType] = $instanceCreator;

        return $this;
    }

    /**
     * Add a visitor that will be called when [@see ClassMetadata] is first loaded
     *
     * @param ClassMetadataVisitor $classMetadataVisitor
     * @return GsonBuilder
     */
    public function addClassMetadataVisitor(ClassMetadataVisitor $classMetadataVisitor): GsonBuilder
    {
        $this->classMetadataVisitors[] = $classMetadataVisitor;

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
     * Require the [@see ExclusionCheck] annotation to use non-cached exclusion strategies
     *
     * @return GsonBuilder
     */
    public function requireExclusionCheckAnnotation(): GsonBuilder
    {
        $this->requireExclusionCheck = true;

        return $this;
    }

    /**
     * Add an [@see ExclusionStrategy]
     *
     * @param ExclusionStrategy $exclusionStrategy
     * @return GsonBuilder
     */
    public function addExclusion(ExclusionStrategy $exclusionStrategy): GsonBuilder
    {
        if (!$exclusionStrategy->shouldCache()) {
            $this->exclusionStrategies[] = $exclusionStrategy;
            return $this;
        }

        if (
            $exclusionStrategy instanceof SerializationExclusionDataAware
            || $exclusionStrategy instanceof DeserializationExclusionDataAware
        ) {
            throw new LogicException('Gson: Cacheable exclusion strategies must not implement *DataAware interfaces');
        }

        $this->cachedExclusionStrategies[] = $exclusionStrategy;

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
     * Set context to use during deserialization
     *
     * @param ReaderContext $context
     * @return GsonBuilder
     */
    public function setReaderContext(ReaderContext $context): GsonBuilder
    {
        $this->readerContext = $context;

        return $this;
    }

    /**
     * Set context to use during serialization
     *
     * @param WriterContext $context
     * @return GsonBuilder
     */
    public function setWriterContext(WriterContext $context): GsonBuilder
    {
        $this->writerContext = $context;

        return $this;
    }

    /**
     * Enable or disable scalar type adapters
     *
     * @param bool $enableScalarAdapters
     * @return GsonBuilder
     */
    public function setEnableScalarAdapters(bool $enableScalarAdapters): GsonBuilder
    {
        $this->enableScalarAdapters = $enableScalarAdapters;

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
     * @throws LogicException
     */
    public function build(): Gson
    {
        if ($this->enableCache === true && ($this->cacheDir === null && $this->cache === null)) {
            throw new LogicException('Cannot enable cache without a cache directory');
        }

        $readerContext = $this->readerContext ?? new ReaderContext();
        $writerContext = $this->writerContext ?? new WriterContext();
        if ($this->enableScalarAdapters !== null) {
            $readerContext->setEnableScalarAdapters($this->enableScalarAdapters);
            $writerContext->setEnableScalarAdapters($this->enableScalarAdapters);
        }

        if ($readerContext->enableScalarAdapters() !== $writerContext->enableScalarAdapters()) {
            throw new LogicException('The "enableScalarAdapter" values for the reader and writer contexts must match');
        }

        $propertyNamingStrategy = $this->propertyNamingStrategy ?? new DefaultPropertyNamingStrategy($this->propertyNamingPolicy);
        $methodNamingStrategy = $this->methodNamingStrategy ?? new UpperCaseMethodNamingStrategy();

        if ($this->cache === null) {
            $this->cache = false === $this->enableCache
                ? CacheProvider::createMemoryCache()
                : CacheProvider::createFileCache($this->cacheDir);
        }

        // no need to cache the annotations as they get cached with the class/properties
        $annotationReader = new AnnotationReaderAdapter(new AnnotationReader(), CacheProvider::createNullCache());
        $excluder = new Excluder();
        $excluder->setVersion($this->version);
        $excluder->setExcludedModifiers($this->excludedModifiers);
        $excluder->setRequireExpose($this->requireExpose);

        foreach ($this->exclusionStrategies as $strategy) {
            $excluder->addExclusionStrategy($strategy);
        }

        foreach ($this->cachedExclusionStrategies as $strategy) {
            $excluder->addCachedExclusionStrategy($strategy);
        }

        $classMetadataFactory = new ClassMetadataFactory(
            new ReflectionPropertySetFactory(),
            $annotationReader,
            new PropertyNamer($propertyNamingStrategy),
            new AccessorMethodProvider($methodNamingStrategy),
            new AccessorStrategyFactory(),
            new TypeTokenFactory(),
            $excluder,
            $this->cache
        );
        $constructorConstructor = new ConstructorConstructor($this->instanceCreators);
        $typeAdapterProvider = new TypeAdapterProvider(
            $this->getTypeAdapterFactories(
                $classMetadataFactory,
                $excluder,
                $constructorConstructor,
                $readerContext->enableScalarAdapters()
            ),
            $constructorConstructor
        );

        return new Gson($typeAdapterProvider, $readerContext, $writerContext);
    }

    /**
     * Merges default factories with user provided factories
     *
     * @param ClassMetadataFactory $classMetadataFactory
     * @param Excluder $excluder
     * @param ConstructorConstructor $constructorConstructor
     * @param bool $enableScalarAdapters
     * @return array|TypeAdapterFactory[]
     */
    private function getTypeAdapterFactories(
        ClassMetadataFactory $classMetadataFactory,
        Excluder $excluder,
        ConstructorConstructor $constructorConstructor,
        bool $enableScalarAdapters
    ): array {
        $scalarFactories = [];
        if ($enableScalarAdapters) {
            $scalarFactories = [
                new StringTypeAdapter(),
                new IntegerTypeAdapter(),
                new FloatTypeAdapter(),
                new BooleanTypeAdapter(),
                new NullTypeAdapter(),
            ];
        }

        return array_merge(
            $this->typeAdapterFactories,
            $scalarFactories,
            [
                new DateTimeTypeAdapterFactory($this->dateTimeFormat),
                new ArrayTypeAdapterFactory($enableScalarAdapters),
                new ReflectionTypeAdapterFactory(
                    $constructorConstructor,
                    $classMetadataFactory,
                    $excluder,
                    $this->requireExclusionCheck,
                    $this->classMetadataVisitors
                ),
                new WildcardTypeAdapterFactory(),
            ]
        );
    }
}
