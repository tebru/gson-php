<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test;

use DateTime;
use Doctrine\Common\Annotations\AnnotationReader;
use Psr\SimpleCache\CacheInterface;
use Tebru\AnnotationReader\AnnotationReaderAdapter;
use Tebru\Gson\Context\ReaderContext;
use Tebru\Gson\Context\WriterContext;
use Tebru\Gson\Internal\AccessorMethodProvider;
use Tebru\Gson\Internal\AccessorStrategyFactory;
use Tebru\Gson\Internal\CacheProvider;
use Tebru\Gson\Internal\ConstructorConstructor;
use Tebru\Gson\Internal\Data\ClassMetadataFactory;
use Tebru\Gson\Internal\Data\PropertyCollection;
use Tebru\Gson\Internal\Data\ReflectionPropertySetFactory;
use Tebru\Gson\Internal\DefaultClassMetadata;
use Tebru\Gson\Internal\DefaultJsonDeserializationContext;
use Tebru\Gson\Internal\DefaultJsonSerializationContext;
use Tebru\Gson\Internal\Excluder;
use Tebru\Gson\Internal\Naming\DefaultPropertyNamingStrategy;
use Tebru\Gson\Internal\Naming\PropertyNamer;
use Tebru\Gson\Internal\Naming\UpperCaseMethodNamingStrategy;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\Internal\TypeTokenFactory;
use Tebru\Gson\PropertyNamingPolicy;
use Tebru\Gson\TypeAdapter\BooleanTypeAdapter;
use Tebru\Gson\TypeAdapter\Factory\ArrayTypeAdapterFactory;
use Tebru\Gson\TypeAdapter\Factory\DateTimeTypeAdapterFactory;
use Tebru\Gson\TypeAdapter\Factory\ReflectionTypeAdapterFactory;
use Tebru\Gson\TypeAdapter\Factory\WildcardTypeAdapterFactory;
use Tebru\Gson\TypeAdapter\FloatTypeAdapter;
use Tebru\Gson\TypeAdapter\IntegerTypeAdapter;
use Tebru\Gson\TypeAdapter\NullTypeAdapter;
use Tebru\Gson\TypeAdapter\StringTypeAdapter;

/**
 * Class MockProvider
 *
 * @author Nate Brunette <n@tebru.net>
 */
class MockProvider
{
    public static function annotationReader(CacheInterface $cache = null)
    {
        if (null === $cache) {
            $cache = CacheProvider::createNullCache();
        }

        return new AnnotationReaderAdapter(new AnnotationReader(), $cache);
    }

    public static function classMetadata($class, PropertyCollection $propertyCollection)
    {
        $annotations = self::annotationReader()->readClass($class, true);
        return new DefaultClassMetadata($class, $annotations, $propertyCollection);
    }

    public static function excluder()
    {
        return new Excluder();
    }

    public static function classMetadataFactory(Excluder $excluder)
    {
        return new ClassMetadataFactory(
            new ReflectionPropertySetFactory(),
            self::annotationReader(),
            new PropertyNamer(new DefaultPropertyNamingStrategy(PropertyNamingPolicy::LOWER_CASE_WITH_UNDERSCORES)),
            new AccessorMethodProvider(new UpperCaseMethodNamingStrategy()),
            new AccessorStrategyFactory(),
            new TypeTokenFactory(),
            $excluder,
            CacheProvider::createNullCache()
        );
    }

    public static function reflectionTypeAdapterFactory(
        Excluder $excluder,
        array $metadataVisitors = [],
        bool $requireCheck = false
    ) {
        return new ReflectionTypeAdapterFactory(
            new ConstructorConstructor(),
            self::classMetadataFactory($excluder),
            $excluder,
            $requireCheck,
            $metadataVisitors
        );
    }

    public static function typeAdapterProvider(
        Excluder $excluder = null,
        array $factories = [],
        ?ReflectionTypeAdapterFactory $reflectionTypeAdapterFactory = null,
        bool $enableScalarTypeAdapters = true
    )
    {
        if (null === $excluder) {
            $excluder = self::excluder();
        }

        if (null === $reflectionTypeAdapterFactory) {
            $reflectionTypeAdapterFactory = new ReflectionTypeAdapterFactory(
                new ConstructorConstructor(),
                self::classMetadataFactory($excluder),
                $excluder,
                false,
                []
            );
        }

        $scalarTypeAdapters = $enableScalarTypeAdapters ? [
            new StringTypeAdapter(),
            new IntegerTypeAdapter(),
            new FloatTypeAdapter(),
            new BooleanTypeAdapter(),
            new NullTypeAdapter(),
        ] : [];

        return new TypeAdapterProvider(
            array_merge(
                $factories,
                $scalarTypeAdapters,
                [
                    new DateTimeTypeAdapterFactory(DateTime::ATOM),
                    new ArrayTypeAdapterFactory(false),
                    $reflectionTypeAdapterFactory,
                    new WildcardTypeAdapterFactory(),
                ]
            ),
            new ConstructorConstructor()
        );
    }

    public static function deserializationContext(Excluder $excluder)
    {
        return new DefaultJsonDeserializationContext(self::typeAdapterProvider($excluder), new ReaderContext());
    }

    public static function serializationContext(Excluder $excluder)
    {
        return new DefaultJsonSerializationContext(self::typeAdapterProvider($excluder), new WriterContext());
    }
}
