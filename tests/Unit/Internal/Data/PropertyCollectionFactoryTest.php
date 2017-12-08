<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\Data;

use Doctrine\Common\Annotations\AnnotationReader;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Cache\Simple\ArrayCache;
use Symfony\Component\Cache\Simple\NullCache;
use Tebru\AnnotationReader\AnnotationReaderAdapter;
use Tebru\Gson\Internal\AccessorMethodProvider;
use Tebru\Gson\Internal\AccessorStrategy\GetByClosure;
use Tebru\Gson\Internal\AccessorStrategy\GetByMethod;
use Tebru\Gson\Internal\AccessorStrategy\GetByPublicProperty;
use Tebru\Gson\Internal\AccessorStrategy\SetByClosure;
use Tebru\Gson\Internal\AccessorStrategy\SetByMethod;
use Tebru\Gson\Internal\AccessorStrategy\SetByNull;
use Tebru\Gson\Internal\AccessorStrategy\SetByPublicProperty;
use Tebru\Gson\Internal\AccessorStrategyFactory;
use Tebru\Gson\Internal\Data\Property;
use Tebru\Gson\Internal\Data\PropertyCollection;
use Tebru\Gson\Internal\Data\PropertyCollectionFactory;
use Tebru\Gson\Internal\Data\ReflectionPropertySetFactory;
use Tebru\Gson\Internal\Excluder;
use Tebru\Gson\Internal\MetadataFactory;
use Tebru\Gson\Internal\Naming\PropertyNamer;
use Tebru\Gson\Internal\Naming\DefaultPropertyNamingStrategy;
use Tebru\Gson\Internal\Naming\UpperCaseMethodNamingStrategy;
use Tebru\Gson\Internal\PhpTypeFactory;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\PropertyNamingPolicy;
use Tebru\Gson\Test\Mock\PropertyCollectionMock;
use Tebru\Gson\Test\MockProvider;
use Tebru\PhpType\TypeToken;

/**
 * Class PropertyCollectionFactoryTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\Data\PropertyCollectionFactory
 */
class PropertyCollectionFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Excluder
     */
    private $excluder;

    /**
     * @var PropertyCollectionFactory
     */
    private $propertyCollectionFactory;

    /**
     * @var TypeAdapterProvider
     */
    private $typeAdapterProvider;

    public function setUp()
    {
        $this->excluder = MockProvider::excluder();
        $this->propertyCollectionFactory = MockProvider::propertyCollectionFactory($this->excluder);
        $this->typeAdapterProvider = MockProvider::typeAdapterProvider($this->excluder);
    }
    public function testCreate()
    {
        $collection = $this->propertyCollectionFactory->create(new TypeToken(PropertyCollectionMock::class));

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
        $annotationReader = new AnnotationReaderAdapter(new AnnotationReader(), new NullCache());
        $cache = new ArrayCache();

        $factory = new PropertyCollectionFactory(
            new ReflectionPropertySetFactory(),
            $annotationReader,
            new MetadataFactory($annotationReader),
            new PropertyNamer(new DefaultPropertyNamingStrategy(PropertyNamingPolicy::LOWER_CASE_WITH_UNDERSCORES)),
            new AccessorMethodProvider(new UpperCaseMethodNamingStrategy()),
            new AccessorStrategyFactory(),
            new PhpTypeFactory(),
            new Excluder(),
            $cache
        );

        $cacheKey = 'gson.properties.'.str_replace('\\', '', PropertyCollectionMock::class);

        // assert data is stored in cache
        $factory->create(new TypeToken(PropertyCollectionMock::class));
        self::assertCount(4, $cache->get($cacheKey)->toArray());

        // overwrite cache
        $cache->set($cacheKey, new PropertyCollection());

        // assert we use the new cache
        $collection = $factory->create(new TypeToken(PropertyCollectionMock::class));
        self::assertCount(0, $collection->toArray());
    }
}
