<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\Data;

use Doctrine\Common\Annotations\AnnotationReader;
use PHPUnit\Framework\TestCase;
use Tebru\AnnotationReader\AnnotationCollection;
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
use Tebru\Gson\Internal\CacheProvider;
use Tebru\Gson\Internal\Data\ClassMetadataFactory;
use Tebru\Gson\Internal\Data\PropertyCollection;
use Tebru\Gson\Internal\Data\ReflectionPropertySetFactory;
use Tebru\Gson\Internal\DefaultClassMetadata;
use Tebru\Gson\Internal\Excluder;
use Tebru\Gson\Internal\Naming\DefaultPropertyNamingStrategy;
use Tebru\Gson\Internal\Naming\PropertyNamer;
use Tebru\Gson\Internal\Naming\UpperCaseMethodNamingStrategy;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\Internal\TypeTokenFactory;
use Tebru\Gson\PropertyNamingPolicy;
use Tebru\Gson\Test\Mock\PropertyCollectionMock;
use Tebru\Gson\Test\MockProvider;
use Tebru\PhpType\TypeToken;

/**
 * Class PropertyCollectionFactoryTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\Data\ClassMetadataFactory
 */
class ClassMetadataFactoryTest extends TestCase
{
    /**
     * @var Excluder
     */
    private $excluder;

    /**
     * @var ClassMetadataFactory
     */
    private $classMetadataFactory;

    /**
     * @var TypeAdapterProvider
     */
    private $typeAdapterProvider;

    public function setUp()
    {
        $this->excluder = MockProvider::excluder();
        $this->classMetadataFactory = MockProvider::classMetadataFactory($this->excluder);
        $this->typeAdapterProvider = MockProvider::typeAdapterProvider($this->excluder);
    }
    public function testCreate(): void
    {
        $classMetadata = $this->classMetadataFactory->create(new TypeToken(PropertyCollectionMock::class));

        self::assertSame(PropertyCollectionMock::class, $classMetadata->getName());

        $elements = $classMetadata->getPropertyCollection()->toArray();

        self::assertCount(4, $elements);

        [$changedAccessors, $changedName, $type, $virtual] = $elements;

        self::assertSame('changedAccessors', $changedAccessors->getName());
        self::assertSame('changed_accessors', $changedAccessors->getSerializedName());
        self::assertSame('boolean', (string) $changedAccessors->getType());
        self::assertAttributeInstanceOf(GetByMethod::class, 'getterStrategy', $changedAccessors);
        self::assertAttributeInstanceOf(SetByMethod::class, 'setterStrategy', $changedAccessors);

        self::assertSame('changedName', $changedName->getName());
        self::assertSame('changedname', $changedName->getSerializedName());
        self::assertSame('?', (string) $changedName->getType());
        self::assertAttributeInstanceOf(GetByPublicProperty::class, 'getterStrategy', $changedName);
        self::assertAttributeInstanceOf(SetByPublicProperty::class, 'setterStrategy', $changedName);

        self::assertSame('type', $type->getName());
        self::assertSame('type', $type->getSerializedName());
        self::assertSame('integer', (string) $type->getType());
        self::assertAttributeInstanceOf(GetByClosure::class, 'getterStrategy', $type);
        self::assertAttributeInstanceOf(SetByClosure::class, 'setterStrategy', $type);

        self::assertSame('virtualProperty', $virtual->getName());
        self::assertSame('new_virtual_property', $virtual->getSerializedName());
        self::assertSame('string', (string) $virtual->getType());
        self::assertAttributeInstanceOf(GetByMethod::class, 'getterStrategy', $virtual);
        self::assertAttributeInstanceOf(SetByNull::class, 'setterStrategy', $virtual);
    }

    public function testCreateUsesCache(): void
    {
        $annotationReader = new AnnotationReaderAdapter(new AnnotationReader(), CacheProvider::createNullCache());
        $cache = CacheProvider::createMemoryCache();

        $factory = new ClassMetadataFactory(
            new ReflectionPropertySetFactory(),
            $annotationReader,
            new PropertyNamer(new DefaultPropertyNamingStrategy(PropertyNamingPolicy::LOWER_CASE_WITH_UNDERSCORES)),
            new AccessorMethodProvider(new UpperCaseMethodNamingStrategy()),
            new AccessorStrategyFactory(),
            new TypeTokenFactory(),
            new Excluder(),
            $cache
        );

        $cacheKey = 'gson.classmetadata.'.str_replace('\\', '', PropertyCollectionMock::class);

        // assert data is stored in cache
        $factory->create(new TypeToken(PropertyCollectionMock::class));
        self::assertCount(4, $cache->get($cacheKey)->getPropertyCollection()->toArray());

        // overwrite cache
        $cache->set($cacheKey, new DefaultClassMetadata('foo', new AnnotationCollection(), new PropertyCollection()));

        // assert we use the new cache
        $collection = $factory->create(new TypeToken(PropertyCollectionMock::class));
        self::assertCount(0, $collection->getPropertyCollection()->toArray());
    }
}
