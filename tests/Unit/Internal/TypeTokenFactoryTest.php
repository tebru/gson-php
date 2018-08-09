<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal;

use ArrayObject;
use PHPUnit_Framework_TestCase;
use ReflectionMethod;
use ReflectionProperty;
use Tebru\AnnotationReader\AnnotationCollection;
use Tebru\Gson\Annotation\Type;
use Tebru\Gson\Internal\TypeTokenFactory;
use Tebru\Gson\Test\Mock\ChildClass;
use Tebru\Gson\Test\Mock\ChildClassParent;
use Tebru\Gson\Test\Mock\ChildClassParent2;
use Tebru\Gson\Test\Mock\ClassWithoutParent;
use Tebru\Gson\Test\Mock\ClassWithParameters;
use Tebru\Gson\Test\Mock\ClassWithParametersInstanceCreator;
use Tebru\Gson\Test\Mock\DocblockType\DocblockAliasable;
use Tebru\Gson\Test\Mock\DocblockType\DocblockFoo;
use Tebru\Gson\Test\Mock\DocblockType\DocblockMock;
use Tebru\Gson\Test\Mock\DocblockType\Globals\MyGlobalClassMock;
use Tebru\Gson\Test\Mock\UserMock;
use Tebru\PhpType\TypeToken;

/**
 * Class PhpTypeFactoryTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\TypeTokenFactory
 */
class TypeTokenFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testCreateFromAnnotation()
    {
        $type = new Type(['value' => ChildClass::class]);
        $annotations = new AnnotationCollection();
        $annotations->add($type);

        $factory = new TypeTokenFactory();
        $phpType = $factory->create($annotations);

        self::assertSame(ChildClass::class, $phpType->getRawType());
    }

    public function testCreateFromSetterTypehint()
    {
        $annotations = new AnnotationCollection();
        $factory = new TypeTokenFactory();
        $setter = new ReflectionMethod(ChildClass::class, 'setWithTypehint');
        $phpType = $factory->create($annotations, null, $setter);

        self::assertSame(UserMock::class, $phpType->getRawType());
    }

    public function testCreateFromGetterReturnType()
    {
        $annotations = new AnnotationCollection();
        $factory = new TypeTokenFactory();
        $getter = new ReflectionMethod(ChildClass::class, 'getWithReturnType');
        $setter = new ReflectionMethod(ChildClass::class, 'setFoo');
        $phpType = $factory->create($annotations, $getter, $setter);

        self::assertSame(UserMock::class, $phpType->getRawType());
    }

    public function testCreateFromSetterDefault()
    {
        $annotations = new AnnotationCollection();
        $factory = new TypeTokenFactory();
        $getter = new ReflectionMethod(ChildClass::class, 'isFoo');
        $setter = new ReflectionMethod(ChildClass::class, 'setFoo');
        $phpType = $factory->create($annotations, $getter, $setter);

        self::assertSame('string', (string) $phpType);
    }

    public function testCreateFromPropertyDefault()
    {
        $annotations = new AnnotationCollection();
        $factory = new TypeTokenFactory();
        $property = new ReflectionProperty(ChildClass::class, 'default');
        $phpType = $factory->create($annotations, null, null, $property);

        self::assertSame('integer', (string)$phpType);
    }

    public function testCreateFromDocblockScalar()
    {
        $annotations = new AnnotationCollection();
        $factory = new TypeTokenFactory();
        $property = new ReflectionProperty(DocblockMock::class, 'scalar');
        $phpType = $factory->create($annotations, null, null, $property);

        self::assertSame(TypeToken::INTEGER, $phpType->getRawType());
    }

    public function testCreateFromDocblockNullableScalar()
    {
        $annotations = new AnnotationCollection();
        $factory = new TypeTokenFactory();
        $property = new ReflectionProperty(DocblockMock::class, 'nullableScalar');
        $phpType = $factory->create($annotations, null, null, $property);

        self::assertSame(TypeToken::STRING, $phpType->getRawType());
    }

    public function testCreateFromDocblockNullableScalar2()
    {
        $annotations = new AnnotationCollection();
        $factory = new TypeTokenFactory();
        $property = new ReflectionProperty(DocblockMock::class, 'nullableScalar2');
        $phpType = $factory->create($annotations, null, null, $property);

        self::assertSame(TypeToken::FLOAT, $phpType->getRawType());
    }

    public function testCreateFromDocblockMixed()
    {
        $annotations = new AnnotationCollection();
        $factory = new TypeTokenFactory();
        $property = new ReflectionProperty(DocblockMock::class, 'mixed');
        $phpType = $factory->create($annotations, null, null, $property);

        self::assertSame(TypeToken::WILDCARD, $phpType->getRawType());
    }

    public function testCreateFromDocblockMultipleTypes()
    {
        $annotations = new AnnotationCollection();
        $factory = new TypeTokenFactory();
        $property = new ReflectionProperty(DocblockMock::class, 'multipleTypes');
        $phpType = $factory->create($annotations, null, null, $property);

        self::assertSame(TypeToken::WILDCARD, $phpType->getRawType());
    }

    public function testCreateFromDocblockMultipleTypes2()
    {
        $annotations = new AnnotationCollection();
        $factory = new TypeTokenFactory();
        $property = new ReflectionProperty(DocblockMock::class, 'multipleTypes2');
        $phpType = $factory->create($annotations, null, null, $property);

        self::assertSame(TypeToken::WILDCARD, $phpType->getRawType());
    }

    public function testCreateFromDocblockClassSameNamespace()
    {
        $annotations = new AnnotationCollection();
        $factory = new TypeTokenFactory();
        $property = new ReflectionProperty(DocblockMock::class, 'classSameNamespace');
        $phpType = $factory->create($annotations, null, null, $property);

        self::assertSame(DocblockFoo::class, $phpType->getRawType());
    }

    public function testCreateFromDocblockClassImported()
    {
        $annotations = new AnnotationCollection();
        $factory = new TypeTokenFactory();
        $property = new ReflectionProperty(DocblockMock::class, 'classImported');
        $phpType = $factory->create($annotations, null, null, $property);

        self::assertSame(ChildClass::class, $phpType->getRawType());
    }

    public function testCreateFromDocblockClassGlobalConflict()
    {
        require __DIR__.'/../../Mock/DocblockType/globals.php';

        $annotations = new AnnotationCollection();
        $factory = new TypeTokenFactory();
        $property = new ReflectionProperty(DocblockMock::class, 'classImportedGlobalConflict');
        $phpType = $factory->create($annotations, null, null, $property);

        self::assertSame(MyGlobalClassMock::class, $phpType->getRawType());
    }

    public function testCreateFromDocblockClassFullName()
    {
        $annotations = new AnnotationCollection();
        $factory = new TypeTokenFactory();
        $property = new ReflectionProperty(DocblockMock::class, 'classFullName');
        $phpType = $factory->create($annotations, null, null, $property);

        self::assertSame(ChildClass::class, $phpType->getRawType());
    }

    public function testCreateFromDocblockClassAliased()
    {
        $annotations = new AnnotationCollection();
        $factory = new TypeTokenFactory();
        $property = new ReflectionProperty(DocblockMock::class, 'classAliased');
        $phpType = $factory->create($annotations, null, null, $property);

        self::assertSame(ChildClassParent::class, $phpType->getRawType());
    }

    public function testCreateFromDocblockClassSameNamespaceAliased()
    {
        $annotations = new AnnotationCollection();
        $factory = new TypeTokenFactory();
        $property = new ReflectionProperty(DocblockMock::class, 'classSameNamespaceAliased');
        $phpType = $factory->create($annotations, null, null, $property);

        self::assertSame(DocblockAliasable::class, $phpType->getRawType());
    }

    public function testCreateFromDocblockClassGroupedOneLine()
    {
        $annotations = new AnnotationCollection();
        $factory = new TypeTokenFactory();
        $property = new ReflectionProperty(DocblockMock::class, 'classGroupOneLine');
        $phpType = $factory->create($annotations, null, null, $property);

        self::assertSame(ChildClassParent2::class, $phpType->getRawType());
    }

    public function testCreateFromDocblockClassGroupedMultipleLines1()
    {
        $annotations = new AnnotationCollection();
        $factory = new TypeTokenFactory();
        $property = new ReflectionProperty(DocblockMock::class, 'classGroupMultipleLines1');
        $phpType = $factory->create($annotations, null, null, $property);

        self::assertSame(ClassWithoutParent::class, $phpType->getRawType());
    }

    public function testCreateFromDocblockClassGroupedMultipleLines2()
    {
        $this->markTestSkipped('Skipping until grouped use statements are supported');

        $annotations = new AnnotationCollection();
        $factory = new TypeTokenFactory();
        $property = new ReflectionProperty(DocblockMock::class, 'classGroupMultipleLines2');
        $phpType = $factory->create($annotations, null, null, $property);

        self::assertSame(ClassWithParametersInstanceCreator::class, $phpType->getRawType());
    }

    public function testCreateFromDocblockClassGroupedMultipleLinesAliased()
    {
        $this->markTestSkipped('Skipping until grouped use statements are supported');

        $annotations = new AnnotationCollection();
        $factory = new TypeTokenFactory();
        $property = new ReflectionProperty(DocblockMock::class, 'classGroupMultipleLinesAliased');
        $phpType = $factory->create($annotations, null, null, $property);

        self::assertSame(ClassWithParameters::class, $phpType->getRawType());
    }

    public function testCreateFromDocblockClassGlobal()
    {
        $annotations = new AnnotationCollection();
        $factory = new TypeTokenFactory();
        $property = new ReflectionProperty(DocblockMock::class, 'classGlobal');
        $phpType = $factory->create($annotations, null, null, $property);

        self::assertSame(ArrayObject::class, $phpType->getRawType());
    }

    public function testCreateFromDocblockArray()
    {
        $annotations = new AnnotationCollection();
        $factory = new TypeTokenFactory();
        $property = new ReflectionProperty(DocblockMock::class, 'array');
        $phpType = $factory->create($annotations, null, null, $property);

        self::assertSame(TypeToken::HASH, $phpType->getRawType());
    }

    public function testCreateFromDocblockTypedArray()
    {
        $annotations = new AnnotationCollection();
        $factory = new TypeTokenFactory();
        $property = new ReflectionProperty(DocblockMock::class, 'typedArray');
        $phpType = $factory->create($annotations, null, null, $property);

        self::assertSame(TypeToken::HASH, $phpType->getRawType());
        self::assertSame(TypeToken::INTEGER, $phpType->getGenerics()[0]->getRawType());
    }

    public function testCreateFromDocblockNestedArray()
    {
        $annotations = new AnnotationCollection();
        $factory = new TypeTokenFactory();
        $property = new ReflectionProperty(DocblockMock::class, 'nestedArray');
        $phpType = $factory->create($annotations, null, null, $property);

        self::assertSame(TypeToken::HASH, $phpType->getRawType());
        self::assertSame(TypeToken::HASH, $phpType->getGenerics()[0]->getRawType());
        self::assertSame(TypeToken::WILDCARD, $phpType->getGenerics()[0]->getGenerics()[0]->getRawType());
    }

    public function testCreateFromDocblockClassArray()
    {
        $annotations = new AnnotationCollection();
        $factory = new TypeTokenFactory();
        $property = new ReflectionProperty(DocblockMock::class, 'classArray');
        $phpType = $factory->create($annotations, null, null, $property);

        self::assertSame(TypeToken::HASH, $phpType->getRawType());
        self::assertSame(DocblockFoo::class, $phpType->getGenerics()[0]->getRawType());
    }

    public function testCreateFromDocblockOnlyNull()
    {
        $annotations = new AnnotationCollection();
        $factory = new TypeTokenFactory();
        $property = new ReflectionProperty(DocblockMock::class, 'onlyNull');
        $phpType = $factory->create($annotations, null, null, $property);

        self::assertSame(TypeToken::NULL, $phpType->getRawType());
    }

    public function testCreateFromDocblockNoTypes()
    {
        $annotations = new AnnotationCollection();
        $factory = new TypeTokenFactory();
        $property = new ReflectionProperty(DocblockMock::class, 'noTypes');
        $phpType = $factory->create($annotations, null, null, $property);

        self::assertSame(TypeToken::WILDCARD, $phpType->getRawType());
    }

    public function testCreateFromDocblockNoTags()
    {
        $annotations = new AnnotationCollection();
        $factory = new TypeTokenFactory();
        $property = new ReflectionProperty(DocblockMock::class, 'noTags');
        $phpType = $factory->create($annotations, null, null, $property);

        self::assertSame(TypeToken::WILDCARD, $phpType->getRawType());
    }

    public function testCreateFromDocblockDifferentGetter()
    {
        $annotations = new AnnotationCollection();
        $factory = new TypeTokenFactory();
        $property = new ReflectionProperty(DocblockMock::class, 'differentGetter');
        $getter = new ReflectionMethod(DocblockMock::class, 'getDifferentGetter');
        $phpType = $factory->create($annotations, $getter, null, $property);

        self::assertSame('array<Tebru\Gson\Test\Mock\DocblockType\DocblockFoo>', (string)$phpType);
    }

    public function testCreateFromDocblockDifferentSetter()
    {
        $annotations = new AnnotationCollection();
        $factory = new TypeTokenFactory();
        $property = new ReflectionProperty(DocblockMock::class, 'differentSetter');
        $setter = new ReflectionMethod(DocblockMock::class, 'setDifferentSetter');
        $phpType = $factory->create($annotations, null, $setter, $property);

        self::assertSame('array<Tebru\Gson\Test\Mock\DocblockType\DocblockFoo>', (string)$phpType);
    }

    public function testCreateFromGetter()
    {
        $annotations = new AnnotationCollection();
        $factory = new TypeTokenFactory();
        $getter = new ReflectionMethod(DocblockMock::class, 'getFoo');
        $phpType = $factory->create($annotations, $getter);

        self::assertSame(TypeToken::INTEGER, $phpType->getRawType());
    }

    public function testCreateFromSetter()
    {
        $annotations = new AnnotationCollection();
        $factory = new TypeTokenFactory();
        $setter = new ReflectionMethod(DocblockMock::class, 'setFoo');
        $phpType = $factory->create($annotations, null, $setter);

        self::assertSame(TypeToken::INTEGER, $phpType->getRawType());
    }

    public function testCreateFromSetterNoVariable()
    {
        $annotations = new AnnotationCollection();
        $factory = new TypeTokenFactory();
        $setter = new ReflectionMethod(DocblockMock::class, 'noVariableName');
        $phpType = $factory->create($annotations, null, $setter);

        self::assertSame(TypeToken::WILDCARD, $phpType->getRawType());
    }

    public function testCreateWildcard()
    {
        $annotations = new AnnotationCollection();
        $factory = new TypeTokenFactory();
        $getter = new ReflectionMethod(ChildClass::class, 'isFoo');
        $setter = new ReflectionMethod(ChildClass::class, 'set_baz');
        $phpType = $factory->create($annotations, $getter, $setter);

        self::assertSame('?', (string) $phpType);
    }
}
