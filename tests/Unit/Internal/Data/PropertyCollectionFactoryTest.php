<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\Data;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\VoidCache;
use PHPUnit_Framework_TestCase;
use Tebru\Gson\Internal\AccessorMethodProvider;
use Tebru\Gson\Internal\AccessorStrategy\GetByClosure;
use Tebru\Gson\Internal\AccessorStrategy\GetByMethod;
use Tebru\Gson\Internal\AccessorStrategy\GetByPublicProperty;
use Tebru\Gson\Internal\AccessorStrategy\SetByClosure;
use Tebru\Gson\Internal\AccessorStrategy\SetByMethod;
use Tebru\Gson\Internal\AccessorStrategy\SetByNull;
use Tebru\Gson\Internal\AccessorStrategy\SetByPublicProperty;
use Tebru\Gson\Internal\AccessorStrategyFactory;
use Tebru\Gson\Internal\ConstructorConstructor;
use Tebru\Gson\Internal\Data\AnnotationCollectionFactory;
use Tebru\Gson\Internal\Data\Property;
use Tebru\Gson\Internal\Data\PropertyCollection;
use Tebru\Gson\Internal\Data\PropertyCollectionFactory;
use Tebru\Gson\Internal\Data\ReflectionPropertySetFactory;
use Tebru\Gson\Internal\Excluder;
use Tebru\Gson\Internal\Naming\PropertyNamer;
use Tebru\Gson\Internal\Naming\SnakePropertyNamingStrategy;
use Tebru\Gson\Internal\Naming\UpperCaseMethodNamingStrategy;
use Tebru\Gson\PhpType;
use Tebru\Gson\Internal\PhpTypeFactory;
use Tebru\Gson\Internal\TypeAdapter\BooleanTypeAdapter;
use Tebru\Gson\Internal\TypeAdapter\Factory\BooleanTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\IntegerTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\ReflectionTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\StringTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\WildcardTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\StringTypeAdapter;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\Test\Mock\ExclusionStrategies\ExcludeClassMockExclusionStrategy;
use Tebru\Gson\Test\Mock\ExclusionStrategies\FooPropertyExclusionStrategy;
use Tebru\Gson\Test\Mock\JsonAdapterMock;
use Tebru\Gson\Test\Mock\PropertyCollectionExclusionMock;
use Tebru\Gson\Test\Mock\PropertyCollectionMock;

/**
 * Class PropertyCollectionFactoryTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\Data\PropertyCollectionFactory
 */
class PropertyCollectionFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $annotationCollectionFactory = new AnnotationCollectionFactory(new AnnotationReader(), new VoidCache());

        $factory = new PropertyCollectionFactory(
            new ReflectionPropertySetFactory(),
            $annotationCollectionFactory,
            new PropertyNamer(new SnakePropertyNamingStrategy()),
            new AccessorMethodProvider(new UpperCaseMethodNamingStrategy()),
            new AccessorStrategyFactory(),
            new PhpTypeFactory(),
            new Excluder($annotationCollectionFactory),
            new VoidCache()
        );

        $typeAdapterProvider = new TypeAdapterProvider([
            new StringTypeAdapterFactory(),
            new BooleanTypeAdapterFactory(),
            new IntegerTypeAdapterFactory(),
            new WildcardTypeAdapterFactory(),
        ]);

        $collection = $factory->create(new PhpType(PropertyCollectionMock::class), $typeAdapterProvider);

        /** @var Property[] $elements */
        $elements = $collection->toArray();

        self::assertCount(4, $elements);

        // todo: change to array destructuring syntax when supported in ide
        list($changedAccessors, $changedName, $type, $virtual) = $elements;

        self::assertSame('changedAccessors', $changedAccessors->getRealName());
        self::assertSame('changed_accessors', $changedAccessors->getSerializedName());
        self::assertSame('boolean', (string) $changedAccessors->getType());
        self::assertAttributeInstanceOf(GetByMethod::class, 'getterStrategy', $changedAccessors);
        self::assertAttributeInstanceOf(SetByMethod::class, 'setterStrategy', $changedAccessors);

        self::assertSame('changedName', $changedName->getRealName());
        self::assertSame('changedname', $changedName->getSerializedName());
        self::assertSame('?', (string) $changedName->getType());
        self::assertAttributeInstanceOf(GetByPublicProperty::class, 'getterStrategy', $changedName);
        self::assertAttributeInstanceOf(SetByPublicProperty::class, 'setterStrategy', $changedName);

        self::assertSame('type', $type->getRealName());
        self::assertSame('type', $type->getSerializedName());
        self::assertSame('integer', (string) $type->getType());
        self::assertAttributeInstanceOf(GetByClosure::class, 'getterStrategy', $type);
        self::assertAttributeInstanceOf(SetByClosure::class, 'setterStrategy', $type);

        self::assertSame('virtualProperty', $virtual->getRealName());
        self::assertSame('new_virtual_property', $virtual->getSerializedName());
        self::assertSame('string', (string) $virtual->getType());
        self::assertAttributeInstanceOf(GetByMethod::class, 'getterStrategy', $virtual);
        self::assertAttributeInstanceOf(SetByNull::class, 'setterStrategy', $virtual);
    }

    public function testCreateUsesCache()
    {
        $annotationCollectionFactory = new AnnotationCollectionFactory(new AnnotationReader(), new VoidCache());
        $cache = new ArrayCache();

        $factory = new PropertyCollectionFactory(
            new ReflectionPropertySetFactory(),
            $annotationCollectionFactory,
            new PropertyNamer(new SnakePropertyNamingStrategy()),
            new AccessorMethodProvider(new UpperCaseMethodNamingStrategy()),
            new AccessorStrategyFactory(),
            new PhpTypeFactory(),
            new Excluder($annotationCollectionFactory),
            $cache
        );

        $typeAdapterProvider = new TypeAdapterProvider([
            new StringTypeAdapterFactory(),
            new BooleanTypeAdapterFactory(),
            new IntegerTypeAdapterFactory(),
            new WildcardTypeAdapterFactory(),
        ]);

        // assert data is stored in cache
        $factory->create(new PhpType(PropertyCollectionMock::class), $typeAdapterProvider);
        self::assertCount(4, $cache->fetch(PropertyCollectionMock::class)->toArray());

        // overwrite cache
        $cache->save(PropertyCollectionMock::class, new PropertyCollection());

        // assert we use the new cache
        $collection = $factory->create(new PhpType(PropertyCollectionMock::class), $typeAdapterProvider);
        self::assertCount(0, $collection->toArray());
    }

    public function testCreateExcludesWillNotUsePropertyExclusionStrategy()
    {
        $annotationCollectionFactory = new AnnotationCollectionFactory(new AnnotationReader(), new VoidCache());
        $excluder = new Excluder($annotationCollectionFactory);
        $excluder->addExclusionStrategy(new FooPropertyExclusionStrategy(), true, true);
        $excluder->addExclusionStrategy(new ExcludeClassMockExclusionStrategy(), true, true);

        $factory = new PropertyCollectionFactory(
            new ReflectionPropertySetFactory(),
            $annotationCollectionFactory,
            new PropertyNamer(new SnakePropertyNamingStrategy()),
            new AccessorMethodProvider(new UpperCaseMethodNamingStrategy()),
            new AccessorStrategyFactory(),
            new PhpTypeFactory(),
            $excluder,
            new VoidCache()
        );

        $typeAdapterProvider = new TypeAdapterProvider([
            new ReflectionTypeAdapterFactory(new ConstructorConstructor(), $factory, $excluder),
            new WildcardTypeAdapterFactory(),
        ]);

        $collection = $factory->create(new PhpType(PropertyCollectionExclusionMock::class), $typeAdapterProvider);

        /** @var Property[] $elements */
        $elements = $collection->toArray();

        self::assertCount(1, $elements);
    }

    public function testCreateUsesJsonAdapter()
    {
        $annotationCollectionFactory = new AnnotationCollectionFactory(new AnnotationReader(), new VoidCache());
        $excluder = new Excluder($annotationCollectionFactory);
        $excluder->setExcludedModifiers(\ReflectionProperty::IS_PUBLIC);

        $factory = new PropertyCollectionFactory(
            new ReflectionPropertySetFactory(),
            $annotationCollectionFactory,
            new PropertyNamer(new SnakePropertyNamingStrategy()),
            new AccessorMethodProvider(new UpperCaseMethodNamingStrategy()),
            new AccessorStrategyFactory(),
            new PhpTypeFactory(),
            $excluder,
            new VoidCache()
        );

        $typeAdapterProvider = new TypeAdapterProvider([]);

        $collection = $factory->create(new PhpType(JsonAdapterMock::class), $typeAdapterProvider);

        /** @var Property[] $elements */
        $elements = $collection->toArray();

        self::assertCount(1, $elements);

        self::assertAttributeInstanceOf(StringTypeAdapter::class, 'typeAdapter', $elements[0]);
    }

    public function testCreateVirtualUsesJsonAdapter()
    {
        $annotationCollectionFactory = new AnnotationCollectionFactory(new AnnotationReader(), new VoidCache());
        $excluder = new Excluder($annotationCollectionFactory);
        $excluder->setExcludedModifiers(\ReflectionProperty::IS_PRIVATE);

        $factory = new PropertyCollectionFactory(
            new ReflectionPropertySetFactory(),
            $annotationCollectionFactory,
            new PropertyNamer(new SnakePropertyNamingStrategy()),
            new AccessorMethodProvider(new UpperCaseMethodNamingStrategy()),
            new AccessorStrategyFactory(),
            new PhpTypeFactory(),
            $excluder,
            new VoidCache()
        );

        $typeAdapterProvider = new TypeAdapterProvider([]);

        $collection = $factory->create(new PhpType(JsonAdapterMock::class), $typeAdapterProvider);

        /** @var Property[] $elements */
        $elements = $collection->toArray();

        self::assertCount(1, $elements);

        self::assertAttributeInstanceOf(BooleanTypeAdapter::class, 'typeAdapter', $elements[0]);
    }
}
