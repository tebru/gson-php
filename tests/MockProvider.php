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
use Tebru\Gson\Internal\AccessorMethodProvider;
use Tebru\Gson\Internal\AccessorStrategyFactory;
use Tebru\Gson\Internal\ConstructorConstructor;
use Tebru\Gson\Internal\Data\PropertyCollectionFactory;
use Tebru\Gson\Internal\Data\ReflectionPropertySetFactory;
use Tebru\Gson\Internal\DefaultJsonDeserializationContext;
use Tebru\Gson\Internal\DefaultJsonSerializationContext;
use Tebru\Gson\Internal\Excluder;
use Tebru\Gson\Internal\MetadataFactory;
use Tebru\Gson\Internal\Naming\PropertyNamer;
use Tebru\Gson\Internal\Naming\DefaultPropertyNamingStrategy;
use Tebru\Gson\Internal\Naming\UpperCaseMethodNamingStrategy;
use Tebru\Gson\Internal\PhpTypeFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\ArrayTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\BooleanTypeAdapterFactory;
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

    public static function metadataFactory()
    {
        return new MetadataFactory(self::annotationReader());
    }

    public static function excluder()
    {
        return new Excluder();
    }

    public static function propertyCollectionFactory(Excluder $excluder)
    {
        return new PropertyCollectionFactory(
            new ReflectionPropertySetFactory(),
            self::annotationReader(),
            self::metadataFactory(),
            new PropertyNamer(new DefaultPropertyNamingStrategy(PropertyNamingPolicy::LOWER_CASE_WITH_UNDERSCORES)),
            new AccessorMethodProvider(new UpperCaseMethodNamingStrategy()),
            new AccessorStrategyFactory(),
            new PhpTypeFactory(),
            $excluder,
            new NullCache()
        );
    }

    public static function reflectionTypeAdapterFactory(Excluder $excluder)
    {
        return new ReflectionTypeAdapterFactory(new ConstructorConstructor(), self::propertyCollectionFactory($excluder), self::metadataFactory(), $excluder);
    }

    public static function typeAdapterProvider(Excluder $excluder = null, array $factories = [])
    {
        if (null === $excluder) {
            $excluder = self::excluder();
        }

        return new TypeAdapterProvider(
            array_merge(
                [
                    new ExcluderTypeAdapterFactory($excluder, self::metadataFactory()),
                    new JsonTypeAdapterFactory(self::annotationReader()),
                ],
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
                    new ReflectionTypeAdapterFactory(
                        new ConstructorConstructor(),
                        self::propertyCollectionFactory($excluder),
                        self::metadataFactory(),
                        $excluder
                    ),
                    new WildcardTypeAdapterFactory(),
                ]
            ),
            new ConstructorConstructor()
        );
    }

    public static function deserializationContext(Excluder $excluder)
    {
        return new DefaultJsonDeserializationContext(self::typeAdapterProvider($excluder));
    }

    public static function serializationContext(Excluder $excluder)
    {
        return new DefaultJsonSerializationContext(self::typeAdapterProvider($excluder));
    }
}
