<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal;

use PHPUnit_Framework_TestCase;
use ReflectionProperty;
use Tebru\AnnotationReader\AnnotationCollection;
use Tebru\Gson\Annotation\Exclude;
use Tebru\Gson\Annotation\Expose;
use Tebru\Gson\Annotation\Since;
use Tebru\Gson\Annotation\Until;
use Tebru\Gson\Internal\AccessorStrategy\GetByPublicProperty;
use Tebru\Gson\Internal\AccessorStrategy\SetByPublicProperty;
use Tebru\Gson\Internal\Data\Property;
use Tebru\Gson\Internal\DefaultExclusionData;
use Tebru\Gson\Internal\Excluder;
use Tebru\Gson\Test\Mock\ExcluderExcludeSerializeMock;
use Tebru\Gson\Test\Mock\ExcluderExposeMock;
use Tebru\Gson\Test\Mock\ExcluderVersionMock;
use Tebru\Gson\Test\Mock\ExclusionStrategies\BarPropertyExclusionStrategy;
use Tebru\Gson\Test\Mock\ExclusionStrategies\ExcludeClassMockExclusionStrategy;
use Tebru\Gson\Test\Mock\ExclusionStrategies\FooExclusionStrategy;
use Tebru\Gson\Test\Mock\ExclusionStrategies\FooPropertyExclusionStrategy;
use Tebru\Gson\Test\Mock\Foo;
use Tebru\Gson\Test\Mock\UserMock;
use Tebru\Gson\Test\MockProvider;
use Tebru\PhpType\TypeToken;

/**
 * Class ExcluderTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\Excluder
 */
class ExcluderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Excluder
     */
    private $excluder;

    /**
     * Set up dependencies
     */
    public function setUp()
    {
        $this->excluder = MockProvider::excluder();
    }

    public function testExcludeClassWithoutVersion()
    {
        $classMetadata = MockProvider::classMetadata(ExcluderVersionMock::class);

        self::assertFalse($this->excluder->excludeClass($classMetadata, true));
        self::assertFalse($this->excluder->excludeClass($classMetadata, false));
    }

    public function testExcludeClassWithVersionLow()
    {
        $this->excluder->setVersion('0.1.0');
        $classMetadata = MockProvider::classMetadata(ExcluderVersionMock::class);
        
        self::assertTrue($this->excluder->excludeClass($classMetadata, true));
        self::assertTrue($this->excluder->excludeClass($classMetadata, false));
    }

    public function testExcludeClassWithVersionEqualSince()
    {
        $this->excluder->setVersion('1');
        $classMetadata = MockProvider::classMetadata(ExcluderVersionMock::class);
        
        self::assertFalse($this->excluder->excludeClass($classMetadata, true));
        self::assertFalse($this->excluder->excludeClass($classMetadata, false));
    }

    public function testExcludeClassWithVersionBetween()
    {
        $this->excluder->setVersion('1.5');
        $classMetadata = MockProvider::classMetadata(ExcluderVersionMock::class);

        self::assertFalse($this->excluder->excludeClass($classMetadata, true));
        self::assertFalse($this->excluder->excludeClass($classMetadata, false));
    }

    public function testExcludeClassWithVersionEqualUntil()
    {
        $this->excluder->setVersion('2');
        $classMetadata = MockProvider::classMetadata(ExcluderVersionMock::class);

        self::assertTrue($this->excluder->excludeClass($classMetadata, true));
        self::assertTrue($this->excluder->excludeClass($classMetadata, false));
    }

    public function testExcludeClassWithVersionHigh()
    {
        $this->excluder->setVersion('3');
        $classMetadata = MockProvider::classMetadata(ExcluderVersionMock::class);

        self::assertTrue($this->excluder->excludeClass($classMetadata, true));
        self::assertTrue($this->excluder->excludeClass($classMetadata, false));
    }

    public function testExcludeClassWithExcludeAnnotation()
    {
        $classMetadata = MockProvider::classMetadata(ExcluderExcludeSerializeMock::class);

        self::assertTrue($this->excluder->excludeClass($classMetadata, true));
        self::assertFalse($this->excluder->excludeClass($classMetadata, false));
    }

    public function testExcludeClassWithExposeAnnotation()
    {
        $this->excluder->setRequireExpose(true);
        $classMetadata = MockProvider::classMetadata(ExcluderExposeMock::class);

        self::assertFalse($this->excluder->excludeClass($classMetadata, true));
        self::assertTrue($this->excluder->excludeClass($classMetadata, false));
    }

    public function testExcludeClassWithStrategySerialization()
    {
        $this->excluder->addExclusionStrategy(new FooExclusionStrategy(), true, false);
        $this->excluder->addExclusionStrategy(new ExcludeClassMockExclusionStrategy(), true, false);
        $classMetadata = MockProvider::classMetadata(ExcluderVersionMock::class);

        self::assertTrue($this->excluder->hasSerializationStrategies());
        self::assertFalse($this->excluder->hasDeserializationStrategies());
        self::assertTrue($this->excluder->excludeClassBySerializationStrategy($classMetadata, new DefaultExclusionData(true, null)));
        self::assertFalse($this->excluder->excludeClassByDeserializationStrategy($classMetadata, new DefaultExclusionData(false, null)));
    }

    public function testExcludeClassWithStrategyDeserialization()
    {
        $this->excluder->addExclusionStrategy(new FooExclusionStrategy(), false, true);
        $this->excluder->addExclusionStrategy(new ExcludeClassMockExclusionStrategy(), false, true);
        $classMetadata = MockProvider::classMetadata(ExcluderVersionMock::class);

        self::assertFalse($this->excluder->hasSerializationStrategies());
        self::assertTrue($this->excluder->hasDeserializationStrategies());
        self::assertFalse($this->excluder->excludeClassBySerializationStrategy($classMetadata, new DefaultExclusionData(true, null)));
        self::assertTrue($this->excluder->excludeClassByDeserializationStrategy($classMetadata, new DefaultExclusionData(false, null)));
    }

    public function testExcludePropertyDefaultModifiers()
    {
        $property = new Property(
            'foo',
            'foo',
            new TypeToken('string'),
            new GetByPublicProperty('foo'),
            new SetByPublicProperty('foo'),
            new AnnotationCollection(),
            ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_STATIC,
            false,
            MockProvider::classMetadata(Foo::class)
        );

        self::assertTrue($this->excluder->excludeProperty($property, true));
        self::assertTrue($this->excluder->excludeProperty($property, false));
    }

    public function testExcludePrivateProperties()
    {
        $this->excluder->setExcludedModifiers(ReflectionProperty::IS_STATIC | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE);

        $property = new Property(
            'foo',
            'foo',
            new TypeToken('string'),
            new GetByPublicProperty('foo'),
            new SetByPublicProperty('foo'),
            new AnnotationCollection(),
            ReflectionProperty::IS_PRIVATE,
            false,
            MockProvider::classMetadata(Foo::class)
        );

        self::assertTrue($this->excluder->excludeProperty($property, true));
        self::assertTrue($this->excluder->excludeProperty($property, false));
    }

    public function testExcludeProtectedProperties()
    {
        $this->excluder->setExcludedModifiers(ReflectionProperty::IS_STATIC | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE);

        $property = new Property(
            'foo',
            'foo',
            new TypeToken('string'),
            new GetByPublicProperty('foo'),
            new SetByPublicProperty('foo'),
            new AnnotationCollection(),
            ReflectionProperty::IS_PROTECTED,
            false,
            MockProvider::classMetadata(Foo::class)
        );

        self::assertTrue($this->excluder->excludeProperty($property, true));
        self::assertTrue($this->excluder->excludeProperty($property, false));
    }

    public function testDoNotExcludeWithoutSinceUntilAnnotations()
    {
        $this->excluder->setVersion(1);

        $property = new Property(
            'foo',
            'foo',
            new TypeToken('string'),
            new GetByPublicProperty('foo'),
            new SetByPublicProperty('foo'),
            new AnnotationCollection(),
            ReflectionProperty::IS_PRIVATE,
            false,
            MockProvider::classMetadata(Foo::class)
        );

        self::assertFalse($this->excluder->excludeProperty($property, true));
        self::assertFalse($this->excluder->excludeProperty($property, false));
    }

    public function testDoNotExcludeWithoutVersion()
    {
        $annotations = new AnnotationCollection();
        $annotations->add(new Until(['value' => '2']));

        $property = new Property(
            'foo',
            'foo',
            new TypeToken('string'),
            new GetByPublicProperty('foo'),
            new SetByPublicProperty('foo'),
            $annotations,
            ReflectionProperty::IS_PRIVATE,
            false,
            MockProvider::classMetadata(Foo::class)
        );

        self::assertFalse($this->excluder->excludeProperty($property, true));
        self::assertFalse($this->excluder->excludeProperty($property, false));
    }

    public function testExcludeBeforeSince()
    {
        $this->excluder->setVersion('1.0.0');

        $annotations = new AnnotationCollection();
        $annotations->add(new Since(['value' => '1.0.1']));

        $property = new Property(
            'foo',
            'foo',
            new TypeToken('string'),
            new GetByPublicProperty('foo'),
            new SetByPublicProperty('foo'),
            $annotations,
            ReflectionProperty::IS_PRIVATE,
            false,
            MockProvider::classMetadata(Foo::class)
        );

        self::assertTrue($this->excluder->excludeProperty($property, true));
        self::assertTrue($this->excluder->excludeProperty($property, false));
    }

    public function testDoNotExcludeEqualToSince()
    {
        $this->excluder->setVersion('1.0.1');

        $annotations = new AnnotationCollection();
        $annotations->add(new Since(['value' => '1.0.1']));

        $property = new Property(
            'foo',
            'foo',
            new TypeToken('string'),
            new GetByPublicProperty('foo'),
            new SetByPublicProperty('foo'),
            $annotations,
            ReflectionProperty::IS_PRIVATE,
            false,
            MockProvider::classMetadata(Foo::class)
        );

        self::assertFalse($this->excluder->excludeProperty($property, true));
        self::assertFalse($this->excluder->excludeProperty($property, false));
    }

    public function testDoNotExcludeAfterSince()
    {
        $this->excluder->setVersion('1.0.2');

        $annotations = new AnnotationCollection();
        $annotations->add(new Since(['value' => '1.0.1']));

        $property = new Property(
            'foo',
            'foo',
            new TypeToken('string'),
            new GetByPublicProperty('foo'),
            new SetByPublicProperty('foo'),
            $annotations,
            ReflectionProperty::IS_PRIVATE,
            false,
            MockProvider::classMetadata(Foo::class)
        );

        self::assertFalse($this->excluder->excludeProperty($property, true));
        self::assertFalse($this->excluder->excludeProperty($property, false));
    }

    public function testDoNotExcludeBeforeUntil()
    {
        $this->excluder->setVersion('1');

        $annotations = new AnnotationCollection();
        $annotations->add(new Until(['value' => '2.0.0']));

        $property = new Property(
            'foo',
            'foo',
            new TypeToken('string'),
            new GetByPublicProperty('foo'),
            new SetByPublicProperty('foo'),
            $annotations,
            ReflectionProperty::IS_PRIVATE,
            false,
            MockProvider::classMetadata(Foo::class)
        );

        self::assertFalse($this->excluder->excludeProperty($property, true));
        self::assertFalse($this->excluder->excludeProperty($property, false));
    }

    public function testExcludeEqualToUntil()
    {
        $this->excluder->setVersion('2.0.0');

        $annotations = new AnnotationCollection();
        $annotations->add(new Until(['value' => '2.0.0']));

        $property = new Property(
            'foo',
            'foo',
            new TypeToken('string'),
            new GetByPublicProperty('foo'),
            new SetByPublicProperty('foo'),
            $annotations,
            false,
            ReflectionProperty::IS_PRIVATE,
            MockProvider::classMetadata(Foo::class)
        );

        self::assertTrue($this->excluder->excludeProperty($property, true));
        self::assertTrue($this->excluder->excludeProperty($property, false));
    }

    public function testExcludeGreaterThanUntil()
    {
        $this->excluder->setVersion('2.0.1');

        $annotations = new AnnotationCollection();
        $annotations->add(new Until(['value' => '2.0.0']));

        $property = new Property(
            'foo',
            'foo',
            new TypeToken('string'),
            new GetByPublicProperty('foo'),
            new SetByPublicProperty('foo'),
            $annotations,
            ReflectionProperty::IS_PRIVATE,
            false,
            MockProvider::classMetadata(Foo::class)
        );

        self::assertTrue($this->excluder->excludeProperty($property, true));
        self::assertTrue($this->excluder->excludeProperty($property, false));
    }

    public function testExcludeWithExcludeAnnotation()
    {
        $annotations = new AnnotationCollection();
        $annotations->add(new Exclude([]));

        $property = new Property(
            'foo',
            'foo',
            new TypeToken('string'),
            new GetByPublicProperty('foo'),
            new SetByPublicProperty('foo'),
            $annotations,
            ReflectionProperty::IS_PRIVATE,
            false,
            MockProvider::classMetadata(Foo::class)
        );

        self::assertTrue($this->excluder->excludeProperty($property, true));
        self::assertTrue($this->excluder->excludeProperty($property, false));
    }

    public function testExcludeWithExcludeAnnotationOnlySerialize()
    {
        $annotations = new AnnotationCollection();
        $annotations->add(new Exclude(['deserialize' => false]));

        $property = new Property(
            'foo',
            'foo',
            new TypeToken('string'),
            new GetByPublicProperty('foo'),
            new SetByPublicProperty('foo'),
            $annotations,
            ReflectionProperty::IS_PRIVATE,
            false,
            MockProvider::classMetadata(Foo::class)
        );

        self::assertTrue($this->excluder->excludeProperty($property, true));
        self::assertFalse($this->excluder->excludeProperty($property, false));
    }

    public function testExcludeWithExcludeAnnotationOnlyDeserialize()
    {
        $annotations = new AnnotationCollection();
        $annotations->add(new Exclude(['serialize' => false]));

        $property = new Property(
            'foo',
            'foo',
            new TypeToken('string'),
            new GetByPublicProperty('foo'),
            new SetByPublicProperty('foo'),
            $annotations,
            ReflectionProperty::IS_PRIVATE,
            false,
            MockProvider::classMetadata(Foo::class)
        );

        self::assertFalse($this->excluder->excludeProperty($property, true));
        self::assertTrue($this->excluder->excludeProperty($property, false));
    }

    public function testDoNotExcludeWithExposeAnnotation()
    {
        $this->excluder->setRequireExpose(true);

        $annotations = new AnnotationCollection();
        $annotations->add(new Expose([]));

        $property = new Property(
            'foo',
            'foo',
            new TypeToken('string'),
            new GetByPublicProperty('foo'),
            new SetByPublicProperty('foo'),
            $annotations,
            ReflectionProperty::IS_PRIVATE,
            false,
            MockProvider::classMetadata(Foo::class)
        );

        self::assertFalse($this->excluder->excludeProperty($property, true));
        self::assertFalse($this->excluder->excludeProperty($property, false));
    }

    public function testDoNotExcludeWithExposeAnnotationOnlySerialize()
    {
        $this->excluder->setRequireExpose(true);

        $annotations = new AnnotationCollection();
        $annotations->add(new Expose(['deserialize' => false]));

        $property = new Property(
            'foo',
            'foo',
            new TypeToken('string'),
            new GetByPublicProperty('foo'),
            new SetByPublicProperty('foo'),
            $annotations,
            ReflectionProperty::IS_PRIVATE,
            false,
            MockProvider::classMetadata(Foo::class)
        );

        self::assertFalse($this->excluder->excludeProperty($property, true));
        self::assertTrue($this->excluder->excludeProperty($property, false));
    }

    public function testDoNotExcludeWithExposeAnnotationOnlyDeserialize()
    {
        $this->excluder->setRequireExpose(true);

        $annotations = new AnnotationCollection();
        $annotations->add(new Expose(['serialize' => false]));

        $property = new Property(
            'foo',
            'foo',
            new TypeToken('string'),
            new GetByPublicProperty('foo'),
            new SetByPublicProperty('foo'),
            $annotations,
            ReflectionProperty::IS_PRIVATE,
            false,
            MockProvider::classMetadata(Foo::class)
        );

        self::assertTrue($this->excluder->excludeProperty($property, true));
        self::assertFalse($this->excluder->excludeProperty($property, false));
    }

    public function testDoNotExcludeWithExposeAnnotationWithoutRequireExpose()
    {
        $annotations = new AnnotationCollection();
        $annotations->add(new Expose(['serialize' => false]));

        $property = new Property(
            'foo',
            'foo',
            new TypeToken('string'),
            new GetByPublicProperty('foo'),
            new SetByPublicProperty('foo'),
            $annotations,
            ReflectionProperty::IS_PRIVATE,
            false,
            MockProvider::classMetadata(Foo::class)
        );

        self::assertFalse($this->excluder->excludeProperty($property, true));
        self::assertFalse($this->excluder->excludeProperty($property, false));
    }

    public function testExcludeWithoutExposeAnnotationWithRequireExpose()
    {
        $this->excluder->setRequireExpose(true);

        $property = new Property(
            'foo',
            'foo',
            new TypeToken('string'),
            new GetByPublicProperty('foo'),
            new SetByPublicProperty('foo'),
            new AnnotationCollection(),
            ReflectionProperty::IS_PRIVATE,
            false,
            MockProvider::classMetadata(Foo::class)
        );

        self::assertTrue($this->excluder->excludeProperty($property, true));
        self::assertTrue($this->excluder->excludeProperty($property, false));
    }

    public function testExcludeFromStrategy()
    {
        $this->excluder->addExclusionStrategy(new BarPropertyExclusionStrategy(), true, true);
        $this->excluder->addExclusionStrategy(new FooPropertyExclusionStrategy(), true, true);

        $property = new Property(
            'foo',
            'foo',
            new TypeToken('string'),
            new GetByPublicProperty('foo'),
            new SetByPublicProperty('foo'),
            new AnnotationCollection(),
            ReflectionProperty::IS_PRIVATE,
            false,
            MockProvider::classMetadata(Foo::class)
        );

        $serializeExclusionData = new DefaultExclusionData(true, new UserMock());
        $deserializeExclusionData = new DefaultExclusionData(false, new UserMock(), ['name' => 'John Doe']);
        self::assertTrue($this->excluder->excludePropertyBySerializationStrategy($property, $serializeExclusionData));
        self::assertTrue($this->excluder->excludePropertyByDeserializationStrategy($property, $deserializeExclusionData));
    }

    public function testExcludeFromStrategyFalse()
    {
        $property = new Property(
            'foo',
            'foo',
            new TypeToken('string'),
            new GetByPublicProperty('foo'),
            new SetByPublicProperty('foo'),
            new AnnotationCollection(),
            ReflectionProperty::IS_PRIVATE,
            false,
            MockProvider::classMetadata(Foo::class)
        );

        $serializeExclusionData = new DefaultExclusionData(true, new UserMock());
        $deserializeExclusionData = new DefaultExclusionData(false, new UserMock(), ['name' => 'John Doe']);
        self::assertFalse($this->excluder->excludePropertyBySerializationStrategy($property, $serializeExclusionData));
        self::assertFalse($this->excluder->excludePropertyByDeserializationStrategy($property, $deserializeExclusionData));
    }
}
