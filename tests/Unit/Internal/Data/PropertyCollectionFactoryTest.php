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
use Tebru\Gson\Internal\AccessorStrategy\SetByPublicProperty;
use Tebru\Gson\Internal\AccessorStrategyFactory;
use Tebru\Gson\Internal\Data\AnnotationCollectionFactory;
use Tebru\Gson\Internal\Data\Property;
use Tebru\Gson\Internal\Data\PropertyCollection;
use Tebru\Gson\Internal\Data\PropertyCollectionFactory;
use Tebru\Gson\Internal\Data\ReflectionPropertySetFactory;
use Tebru\Gson\Internal\Excluder;
use Tebru\Gson\Internal\Naming\PropertyNamer;
use Tebru\Gson\Internal\Naming\SnakePropertyNamingStrategy;
use Tebru\Gson\Internal\Naming\UpperCaseMethodNamingStrategy;
use Tebru\Gson\Internal\PhpType;
use Tebru\Gson\Internal\PhpTypeFactory;
use Tebru\Gson\Test\Mock\ExclusionStrategies\ExcludeClassMockExclusionStrategy;
use Tebru\Gson\Test\Mock\ExclusionStrategies\FooPropertyExclusionStrategy;
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
        $annotationCollectionFactory = new AnnotationCollectionFactory(new AnnotationReader());

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

        $collection = $factory->create(new PhpType(PropertyCollectionMock::class));

        /** @var Property[] $elements */
        $elements = $collection->toArray();

        self::assertCount(3, $elements);

        // todo: change to array destructuring syntax when supported in ide
        list($changedAccessors, $changedName, $type) = $elements;

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
    }

    public function testCreateUsesCache()
    {
        $annotationCollectionFactory = new AnnotationCollectionFactory(new AnnotationReader());
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

        // assert data is stored in cache
        $factory->create(new PhpType(PropertyCollectionMock::class));
        self::assertCount(3, $cache->fetch(PropertyCollectionMock::class)->toArray());

        // overwrite cache
        $cache->save(PropertyCollectionMock::class, new PropertyCollection());
        $reflectionProperty = new \ReflectionProperty(PropertyCollectionFactory::class, 'collectionCache');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($factory, []);

        // assert we use the new cache
        $collection = $factory->create(new PhpType(PropertyCollectionMock::class));
        self::assertCount(0, $collection->toArray());
    }

    public function testCreateUsesMemoryCache()
    {
        $annotationCollectionFactory = new AnnotationCollectionFactory(new AnnotationReader());
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

        // assert data is stored in cache
        $factory->create(new PhpType(PropertyCollectionMock::class));
        self::assertCount(3, $cache->fetch(PropertyCollectionMock::class)->toArray());

        // overwrite cache
        $cache->save(PropertyCollectionMock::class, new PropertyCollection());

        // assert we use the new cache
        $collection = $factory->create(new PhpType(PropertyCollectionMock::class));
        self::assertCount(3, $collection->toArray());
    }

    public function testCreateExcludes()
    {
        $annotationCollectionFactory = new AnnotationCollectionFactory(new AnnotationReader());
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

        $collection = $factory->create(new PhpType(PropertyCollectionExclusionMock::class));

        /** @var Property[] $elements */
        $elements = $collection->toArray();

        self::assertCount(0, $elements);
    }
}
