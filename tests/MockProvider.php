<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test;

use DateTime;
use Doctrine\Common\Annotations\AnnotationReader;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Simple\NullCache;
use Tebru\AnnotationReader\AnnotationReaderAdapter;
use Tebru\Gson\TypeAdapter\Factory\ArrayTypeAdapterFactory;
use Tebru\Gson\TypeAdapter\Factory\BooleanTypeAdapterFactory;
use Tebru\Gson\TypeAdapter\Factory\DateTimeTypeAdapterFactory;
use Tebru\Gson\TypeAdapter\Factory\FloatTypeAdapterFactory;
use Tebru\Gson\TypeAdapter\Factory\IntegerTypeAdapterFactory;
use Tebru\Gson\TypeAdapter\Factory\JsonElementTypeAdapterFactory;
use Tebru\Gson\TypeAdapter\Factory\NullTypeAdapterFactory;
use Tebru\Gson\TypeAdapter\Factory\ReflectionTypeAdapterFactory;
use Tebru\Gson\TypeAdapter\Factory\StringTypeAdapterFactory;
use Tebru\Gson\TypeAdapter\Factory\WildcardTypeAdapterFactory;
use Tebru\Gson\Internal\AccessorMethodProvider;
use Tebru\Gson\Internal\AccessorStrategyFactory;
use Tebru\Gson\Internal\ConstructorConstructor;
use Tebru\Gson\Internal\Data\ClassMetadataFactory;
use Tebru\Gson\Internal\Data\PropertyCollection;
use Tebru\Gson\Internal\Data\ReflectionPropertySetFactory;
use Tebru\Gson\Internal\DefaultClassMetadata;
use Tebru\Gson\Internal\DefaultJsonDeserializationContext;
use Tebru\Gson\Internal\DefaultJsonSerializationContext;
use Tebru\Gson\Internal\DefaultReaderContext;
use Tebru\Gson\Internal\Excluder;
use Tebru\Gson\Internal\Naming\DefaultPropertyNamingStrategy;
use Tebru\Gson\Internal\Naming\PropertyNamer;
use Tebru\Gson\Internal\Naming\UpperCaseMethodNamingStrategy;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\Internal\TypeTokenFactory;
use Tebru\Gson\PropertyNamingPolicy;

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
            $cache = new NullCache();
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
            new NullCache()
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

    public static function typeAdapterProvider(Excluder $excluder = null, array $factories = [], ?ReflectionTypeAdapterFactory $reflectionTypeAdapterFactory = null)
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

        return new TypeAdapterProvider(
            array_merge(
                $factories,
                [
                    new StringTypeAdapterFactory(),
                    new IntegerTypeAdapterFactory(),
                    new FloatTypeAdapterFactory(),
                    new BooleanTypeAdapterFactory(),
                    new NullTypeAdapterFactory(),
                    new DateTimeTypeAdapterFactory(DateTime::ATOM),
                    new ArrayTypeAdapterFactory(),
                    new JsonElementTypeAdapterFactory(),
                    $reflectionTypeAdapterFactory,
                    new WildcardTypeAdapterFactory(),
                ]
            ),
            new ConstructorConstructor()
        );
    }

    public static function deserializationContext(Excluder $excluder)
    {
        return new DefaultJsonDeserializationContext(self::typeAdapterProvider($excluder), new DefaultReaderContext());
    }

    public static function serializationContext(Excluder $excluder)
    {
        return new DefaultJsonSerializationContext(self::typeAdapterProvider($excluder), false);
    }
}
