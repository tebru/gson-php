<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter\Factory;

use Doctrine\Common\Annotations\AnnotationReader;

use Doctrine\Common\Cache\VoidCache;
use PHPUnit_Framework_TestCase;
use Tebru\Gson\Internal\Data\AnnotationCollectionFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\JsonTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\StringTypeAdapter;

use Tebru\Gson\Test\Mock\ChildClass;
use Tebru\Gson\Test\Mock\JsonAdapterClassMock;
use Tebru\Gson\Test\MockProvider;
use Tebru\PhpType\TypeToken;

/**
 * Class JsonTypeAdapterFactoryTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\TypeAdapter\Factory\JsonTypeAdapterFactory
 */
class JsonTypeAdapterFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testSupportsTrue()
    {
        $factory = new JsonTypeAdapterFactory(new AnnotationCollectionFactory(new AnnotationReader(), new VoidCache()));

        self::assertTrue($factory->supports(new TypeToken(JsonAdapterClassMock::class)));
    }

    public function testSupportsFalse()
    {
        $factory = new JsonTypeAdapterFactory(new AnnotationCollectionFactory(new AnnotationReader(), new VoidCache()));

        self::assertFalse($factory->supports(new TypeToken(ChildClass::class)));
    }

    public function testSupportsNonObject()
    {
        $factory = new JsonTypeAdapterFactory(new AnnotationCollectionFactory(new AnnotationReader(), new VoidCache()));

        self::assertFalse($factory->supports(new TypeToken('string')));
    }

    public function testNotValidForPseudoClass()
    {
        $factory = new JsonTypeAdapterFactory(new AnnotationCollectionFactory(new AnnotationReader(), new VoidCache()));

        self::assertFalse($factory->supports(new TypeToken('String')));
    }

    public function testCreate()
    {
        $factory = new JsonTypeAdapterFactory(new AnnotationCollectionFactory(new AnnotationReader(), new VoidCache()));

        self::assertInstanceOf(StringTypeAdapter::class, $factory->create(new TypeToken(JsonAdapterClassMock::class), MockProvider::typeAdapterProvider()));
    }
}
