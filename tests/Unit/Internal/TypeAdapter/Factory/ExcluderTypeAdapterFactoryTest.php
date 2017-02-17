<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\TypeAdapter\Factory;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\VoidCache;
use PHPUnit_Framework_TestCase;
use Tebru\Gson\Internal\Data\AnnotationCollectionFactory;
use Tebru\Gson\Internal\Excluder;
use Tebru\Gson\PhpType;
use Tebru\Gson\Internal\TypeAdapter\ExcluderTypeAdapter;
use Tebru\Gson\Internal\TypeAdapter\Factory\ExcluderTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\Test\Mock\ChildClass;
use Tebru\Gson\Test\Mock\ExcluderExcludeMock;
use Tebru\Gson\Test\Mock\ExcluderExposeMock;

/**
 * Class ExcluderTypeAdapterFactoryTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\TypeAdapter\Factory\ExcluderTypeAdapterFactory
 */
class ExcluderTypeAdapterFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testValidSupportsOnlySerialization()
    {
        $excluder = $this->excluder();

        $factory = new ExcluderTypeAdapterFactory($excluder);

        self::assertTrue($factory->supports(new PhpType(ExcluderExcludeMock::class)));
    }

    public function testValidSupportsOnlyDeserialization()
    {
        $excluder = $this->excluder();
        $excluder->setRequireExpose(true);

        $factory = new ExcluderTypeAdapterFactory($excluder);

        self::assertTrue($factory->supports(new PhpType(ExcluderExposeMock::class)));
    }

    public function testValidSupportsBoth()
    {
        $excluder = $this->excluder();
        $excluder->setRequireExpose(true);

        $factory = new ExcluderTypeAdapterFactory($excluder);

        self::assertTrue($factory->supports(new PhpType(ChildClass::class)));
    }

    public function testValidSupportsFalse()
    {
        $excluder = $this->excluder();

        $factory = new ExcluderTypeAdapterFactory($excluder);

        self::assertFalse($factory->supports(new PhpType(ChildClass::class)));
    }

    public function testValidSupportsNonObject()
    {
        $excluder = $this->excluder();

        $factory = new ExcluderTypeAdapterFactory($excluder);

        self::assertFalse($factory->supports(new PhpType('string')));
    }

    public function testCreate()
    {
        $excluder = $this->excluder();
        $excluder->setRequireExpose(true);

        $factory = new ExcluderTypeAdapterFactory($excluder);
        $phpType = new PhpType(ChildClass::class);
        $typeAdapterProvider = new TypeAdapterProvider([], new ArrayCache());
        $adapter = $factory->create($phpType, $typeAdapterProvider);

        self::assertInstanceOf(ExcluderTypeAdapter::class, $adapter);
        self::assertAttributeSame($phpType, 'phpType', $adapter);
        self::assertAttributeSame($typeAdapterProvider, 'typeAdapterProvider', $adapter);
        self::assertAttributeSame(true, 'skipSerialize', $adapter);
        self::assertAttributeSame(true, 'skipDeserialize', $adapter);
    }

    private function excluder(): Excluder
    {
        return new Excluder(new AnnotationCollectionFactory(new AnnotationReader(), new VoidCache()));
    }
}
