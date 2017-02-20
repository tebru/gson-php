<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal;

use PHPUnit_Framework_TestCase;
use ReflectionMethod;
use Tebru\Gson\Annotation\Type;
use Tebru\Gson\Internal\Data\AnnotationSet;
use Tebru\Gson\Internal\PhpTypeFactory;
use Tebru\Gson\Test\Mock\ChildClass;
use Tebru\Gson\Test\Mock\UserMock;

/**
 * Class PhpTypeFactoryTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\PhpTypeFactory
 */
class PhpTypeFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testCreateFromAnnotation()
    {
        $type = new Type(['value' => ChildClass::class]);
        $annotations = new AnnotationSet();
        $annotations->addAnnotation($type, AnnotationSet::TYPE_PROPERTY);

        $factory = new PhpTypeFactory();
        $phpType = $factory->create($annotations, AnnotationSet::TYPE_PROPERTY);

        self::assertSame(ChildClass::class, $phpType->getType());
    }

    public function testCreateFromSetterTypehint()
    {
        $annotations = new AnnotationSet();
        $factory = new PhpTypeFactory();
        $setter = new ReflectionMethod(ChildClass::class, 'setWithTypehint');
        $phpType = $factory->create($annotations, AnnotationSet::TYPE_PROPERTY, null, $setter);

        self::assertSame(UserMock::class, $phpType->getType());
    }

    public function testCreateFromGetterReturnType()
    {
        $annotations = new AnnotationSet();
        $factory = new PhpTypeFactory();
        $getter = new ReflectionMethod(ChildClass::class, 'getWithReturnType');
        $setter = new ReflectionMethod(ChildClass::class, 'setFoo');
        $phpType = $factory->create($annotations, AnnotationSet::TYPE_PROPERTY, $getter, $setter);

        self::assertSame(UserMock::class, $phpType->getType());
    }

    public function testCreateFromSetterDefault()
    {
        $annotations = new AnnotationSet();
        $factory = new PhpTypeFactory();
        $getter = new ReflectionMethod(ChildClass::class, 'isFoo');
        $setter = new ReflectionMethod(ChildClass::class, 'setFoo');
        $phpType = $factory->create($annotations, AnnotationSet::TYPE_PROPERTY, $getter, $setter);

        self::assertSame('string', (string) $phpType);
    }

    public function testCreateWildcard()
    {
        $annotations = new AnnotationSet();
        $factory = new PhpTypeFactory();
        $getter = new ReflectionMethod(ChildClass::class, 'isFoo');
        $setter = new ReflectionMethod(ChildClass::class, 'set_baz');
        $phpType = $factory->create($annotations, AnnotationSet::TYPE_PROPERTY, $getter, $setter);

        self::assertSame('?', (string) $phpType);
    }
}
