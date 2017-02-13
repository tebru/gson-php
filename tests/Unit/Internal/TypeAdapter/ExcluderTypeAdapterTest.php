<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\VoidCache;
use PHPUnit_Framework_TestCase;
use Tebru\Gson\Internal\Data\AnnotationCollectionFactory;
use Tebru\Gson\Internal\Excluder;
use Tebru\Gson\Internal\PhpType;
use Tebru\Gson\Internal\TypeAdapter\ExcluderTypeAdapter;
use Tebru\Gson\Internal\TypeAdapter\Factory\ExcluderTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapter\Factory\HashMapTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\Test\Mock\ExclusionStrategies\FooExclusionStrategy;
use Tebru\Gson\Test\Mock\Foo;
use Tebru\Gson\Test\Mock\TypeAdapter\FooTypeAdapterFactory;

/**
 * Class ExcluderTypeAdapterTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\TypeAdapter\ExcluderTypeAdapter
 */
class ExcluderTypeAdapterTest extends PHPUnit_Framework_TestCase
{
    public function testDeserializeSkips()
    {
        $excluder = new Excluder(new AnnotationCollectionFactory(new AnnotationReader(), new VoidCache()));
        $excluder->addExclusionStrategy(new FooExclusionStrategy(), false, true);

        $typeAdapterProvider = new TypeAdapterProvider([
            new ExcluderTypeAdapterFactory($excluder),
            new HashMapTypeAdapterFactory(),
        ]);
        $adapter = $typeAdapterProvider->getAdapter(new PhpType('Foo'));

        self::assertNull($adapter->readFromJson('{}'));
    }

    public function testDeserializeDelegates()
    {
        $excluder = new Excluder(new AnnotationCollectionFactory(new AnnotationReader(), new VoidCache()));
        $excluder->addExclusionStrategy(new FooExclusionStrategy(), true, false);

        $typeAdapterProvider = new TypeAdapterProvider([
            new ExcluderTypeAdapterFactory($excluder),
            new FooTypeAdapterFactory(),
            new HashMapTypeAdapterFactory(),
        ]);

        /** @var ExcluderTypeAdapter $adapter */
        $adapter = $typeAdapterProvider->getAdapter(new PhpType('Foo'));

        self::assertEquals(new Foo(), $adapter->readFromJson('{}'));
    }
}
