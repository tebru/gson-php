<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\VoidCache;
use PHPUnit_Framework_TestCase;
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
use Tebru\Gson\Internal\TypeAdapter\Factory\NullTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\StringTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\WildcardTypeAdapterFactory;
use Tebru\Gson\PhpType;
use Tebru\Gson\Internal\PhpTypeFactory;
use Tebru\Gson\Internal\TypeAdapter\ExcluderTypeAdapter;
use Tebru\Gson\Internal\TypeAdapter\Factory\ExcluderTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\ReflectionTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\Test\Mock\ExcluderExcludeDeserializeMock;
use Tebru\Gson\Test\Mock\ExcluderExcludeSerializeMock;
use Tebru\Gson\Test\Mock\TypeAdapter\FooTypeAdapterFactory;

/**
 * Class ExcluderTypeAdapterTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\TypeAdapter\ExcluderTypeAdapter
 * @covers \Tebru\Gson\TypeAdapter
 */
class ExcluderTypeAdapterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Excluder
     */
    private $excluder;

    /**
     * @var TypeAdapterProvider
     */
    private $typeAdapterProvider;

    /**
     * Set up test dependencies
     */
    public function setUp()
    {
        $this->excluder = new Excluder();

        $annotationCollectionFactory = new AnnotationCollectionFactory(new AnnotationReader(), new VoidCache());
        $metadataFactory = new MetadataFactory($annotationCollectionFactory);
        
        $propertyCollectionFactory = new PropertyCollectionFactory(
            new ReflectionPropertySetFactory(),
            $annotationCollectionFactory,
            $metadataFactory,
            new PropertyNamer(new SnakePropertyNamingStrategy()),
            new AccessorMethodProvider(new UpperCaseMethodNamingStrategy()),
            new AccessorStrategyFactory(),
            new PhpTypeFactory(),
            $this->excluder,
            new ArrayCache()
        );

        $reflectionTypeAdadpterFactory = new ReflectionTypeAdapterFactory(
            new ConstructorConstructor(),
            $propertyCollectionFactory,
            $metadataFactory
        );

        $this->typeAdapterProvider = new TypeAdapterProvider(
            [
                new ExcluderTypeAdapterFactory($this->excluder, $metadataFactory),
                new StringTypeAdapterFactory(),
                new NullTypeAdapterFactory(),
                new FooTypeAdapterFactory(),
                $reflectionTypeAdadpterFactory,
                new WildcardTypeAdapterFactory(),
            ],
            new ArrayCache()
        );
    }

    public function testDeserializeSkips()
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new PhpType(ExcluderExcludeDeserializeMock::class));

        self::assertNull($adapter->readFromJson('{}'));
    }

    public function testDeserializeDelegates()
    {
        $this->excluder->setVersion('2');

        /** @var ExcluderTypeAdapter $adapter */
        $adapter = $this->typeAdapterProvider->getAdapter(new PhpType(ExcluderExcludeSerializeMock::class));

        self::assertEquals(new ExcluderExcludeSerializeMock(), $adapter->readFromJson('{"foo": null}'));
    }

    public function testSerializeSkips()
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new PhpType(ExcluderExcludeSerializeMock::class));

        self::assertSame('null', $adapter->writeToJson(new ExcluderExcludeSerializeMock(), false));
    }

    public function testSerializeDelegates()
    {
        $adapter = $this->typeAdapterProvider->getAdapter(new PhpType(ExcluderExcludeDeserializeMock::class));

        self::assertSame('{}', $adapter->writeToJson(new ExcluderExcludeDeserializeMock(), false));
    }
}
