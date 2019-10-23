<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal;

use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use stdClass;
use Tebru\AnnotationReader\AnnotationCollection;
use Tebru\Gson\Annotation\Exclude;
use Tebru\Gson\Annotation\Expose;
use Tebru\Gson\Annotation\Since;
use Tebru\Gson\Annotation\Until;
use Tebru\Gson\Context\ReaderContext;
use Tebru\Gson\Context\WriterContext;
use Tebru\Gson\Internal\AccessorStrategy\GetByPublicProperty;
use Tebru\Gson\Internal\AccessorStrategy\SetByPublicProperty;
use Tebru\Gson\Internal\Data\Property;
use Tebru\Gson\Internal\Data\PropertyCollection;
use Tebru\Gson\Internal\DefaultDeserializationExclusionData;
use Tebru\Gson\Internal\DefaultSerializationExclusionData;
use Tebru\Gson\Internal\Excluder;
use Tebru\Gson\Test\Mock\ExcluderExcludeSerializeMock;
use Tebru\Gson\Test\Mock\ExcluderExposeMock;
use Tebru\Gson\Test\Mock\ExcluderVersionMock;
use Tebru\Gson\Test\Mock\ExclusionStrategies\BarPropertyExclusionStrategy;
use Tebru\Gson\Test\Mock\ExclusionStrategies\ExcludeAllExclusionStrategy;
use Tebru\Gson\Test\Mock\ExclusionStrategies\FooDeserializationExclusionStrategy;
use Tebru\Gson\Test\Mock\ExclusionStrategies\FooPropertyExclusionStrategy;
use Tebru\Gson\Test\Mock\ExclusionStrategies\FooSerializationExclusionStrategy;
use Tebru\Gson\Test\Mock\Foo;
use Tebru\Gson\Test\Mock\GsonMockChild;
use Tebru\Gson\Test\MockProvider;
use Tebru\PhpType\TypeToken;

/**
 * Class ExcluderTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\Excluder
 */
class ExcluderTest extends TestCase
{
    /**
     * @var Excluder
     */
    private $excluder;

    /**
     * @var PropertyCollection
     */
    private $propertyCollection;

    /**
     * Set up dependencies
     */
    public function setUp()
    {
        $this->excluder = MockProvider::excluder();
        $this->propertyCollection = new PropertyCollection();
    }

    public function testExcludeClassWithoutVersion(): void
    {
        $classMetadata = MockProvider::classMetadata(ExcluderVersionMock::class, $this->propertyCollection);

        self::assertFalse($this->excluder->excludeClassSerialize($classMetadata));
        self::assertFalse($this->excluder->excludeClassDeserialize($classMetadata));
    }

    public function testExcludeClassWithVersionLow(): void
    {
        $this->excluder->setVersion('0.1.0');
        $classMetadata = MockProvider::classMetadata(ExcluderVersionMock::class, $this->propertyCollection);
        
        self::assertTrue($this->excluder->excludeClassSerialize($classMetadata));
        self::assertTrue($this->excluder->excludeClassDeserialize($classMetadata));
    }

    public function testExcludeClassWithVersionEqualSince(): void
    {
        $this->excluder->setVersion('1');
        $classMetadata = MockProvider::classMetadata(ExcluderVersionMock::class, $this->propertyCollection);
        
        self::assertFalse($this->excluder->excludeClassSerialize($classMetadata));
        self::assertFalse($this->excluder->excludeClassDeserialize($classMetadata));
    }

    public function testExcludeClassWithVersionBetween(): void
    {
        $this->excluder->setVersion('1.5');
        $classMetadata = MockProvider::classMetadata(ExcluderVersionMock::class, $this->propertyCollection);

        self::assertFalse($this->excluder->excludeClassSerialize($classMetadata));
        self::assertFalse($this->excluder->excludeClassDeserialize($classMetadata));
    }

    public function testExcludeClassWithVersionEqualUntil(): void
    {
        $this->excluder->setVersion('2');
        $classMetadata = MockProvider::classMetadata(ExcluderVersionMock::class, $this->propertyCollection);

        self::assertTrue($this->excluder->excludeClassSerialize($classMetadata));
        self::assertTrue($this->excluder->excludeClassDeserialize($classMetadata));
    }

    public function testExcludeClassWithVersionHigh(): void
    {
        $this->excluder->setVersion('3');
        $classMetadata = MockProvider::classMetadata(ExcluderVersionMock::class, $this->propertyCollection);

        self::assertTrue($this->excluder->excludeClassSerialize($classMetadata));
        self::assertTrue($this->excluder->excludeClassDeserialize($classMetadata));
    }

    public function testExcludeClassWithExcludeAnnotation(): void
    {
        $classMetadata = MockProvider::classMetadata(ExcluderExcludeSerializeMock::class, $this->propertyCollection);

        self::assertTrue($this->excluder->excludeClassSerialize($classMetadata));
        self::assertFalse($this->excluder->excludeClassDeserialize($classMetadata));
    }

    public function testExcludeClassWithExposeAnnotation(): void
    {
        $this->excluder->setRequireExpose(true);
        $classMetadata = MockProvider::classMetadata(ExcluderExposeMock::class, $this->propertyCollection);

        self::assertFalse($this->excluder->excludeClassSerialize($classMetadata));
        self::assertTrue($this->excluder->excludeClassDeserialize($classMetadata));
    }

    public function testDoNotExcludeClassWithPropertyExposed(): void
    {
        $propertyAnnotations = new AnnotationCollection();
        $propertyAnnotations->add(new Expose([]));
        $property = new Property(
            'foo',
            'foo',
            new TypeToken('string'),
            new GetByPublicProperty('foo'),
            new SetByPublicProperty('foo'),
            $propertyAnnotations,
            ReflectionProperty::IS_PRIVATE,
            false,
            MockProvider::classMetadata(Foo::class, $this->propertyCollection)
        );
        $this->propertyCollection->add($property);
        $classMetadata = MockProvider::classMetadata(GsonMockChild::class, $this->propertyCollection);

        self::assertFalse($this->excluder->excludeClassSerialize($classMetadata));
        self::assertFalse($this->excluder->excludeClassDeserialize($classMetadata));
    }

    public function testExcludeClassWithStrategySerialization(): void
    {
        $this->excluder->addExclusionStrategy(new FooSerializationExclusionStrategy());
        $classMetadata = MockProvider::classMetadata(Foo::class, $this->propertyCollection);

        self::assertTrue($this->excluder->hasClassSerializationStrategies());
        self::assertFalse($this->excluder->hasClassDeserializationStrategies());
        self::assertFalse($this->excluder->hasPropertySerializationStrategies());
        self::assertFalse($this->excluder->hasPropertyDeserializationStrategies());
        self::assertTrue($this->excluder->excludeClassBySerializationStrategy($classMetadata));
        self::assertFalse($this->excluder->excludeClassByDeserializationStrategy($classMetadata));
    }

    public function testExcludeClassWithStrategyDeserialization(): void
    {
        $this->excluder->addExclusionStrategy(new FooDeserializationExclusionStrategy());
        $classMetadata = MockProvider::classMetadata(Foo::class, $this->propertyCollection);

        self::assertFalse($this->excluder->hasClassSerializationStrategies());
        self::assertTrue($this->excluder->hasClassDeserializationStrategies());
        self::assertFalse($this->excluder->hasPropertySerializationStrategies());
        self::assertFalse($this->excluder->hasPropertyDeserializationStrategies());
        self::assertFalse($this->excluder->excludeClassBySerializationStrategy($classMetadata));
        self::assertTrue($this->excluder->excludeClassByDeserializationStrategy($classMetadata));
    }

    public function testExcludeCacheable(): void
    {
        $this->excluder->addCachedExclusionStrategy(new ExcludeAllExclusionStrategy());
        $classMetadata = MockProvider::classMetadata(Foo::class, $this->propertyCollection);
        $property = new Property(
            'foo',
            'foo',
            new TypeToken('string'),
            new GetByPublicProperty('foo'),
            new SetByPublicProperty('foo'),
            new AnnotationCollection(),
            ReflectionProperty::IS_PRIVATE,
            false,
            MockProvider::classMetadata(Foo::class, $this->propertyCollection)
        );
        $this->propertyCollection->add($property);

        self::assertTrue($this->excluder->excludeClassSerialize($classMetadata));
        self::assertTrue($this->excluder->excludeClassDeserialize($classMetadata));
        self::assertTrue($this->excluder->excludePropertySerialize($property));
        self::assertTrue($this->excluder->excludePropertyDeserialize($property));
    }

    public function testClassDataAware(): void
    {
        $strategy = new ExcludeAllExclusionStrategy();
        $this->excluder->addExclusionStrategy($strategy);
        $serializationData = new DefaultSerializationExclusionData(new stdClass(), new WriterContext());
        $deserializationData = new DefaultDeserializationExclusionData(new stdClass(), new ReaderContext());
        $this->excluder->applyClassSerializationExclusionData($serializationData);
        $this->excluder->applyClassDeserializationExclusionData($deserializationData);
        self::assertTrue($strategy->calledSerialize);
        self::assertTrue($strategy->calledDeserialize);
    }

    public function testPropertyDataAware(): void
    {
        $strategy = new ExcludeAllExclusionStrategy();
        $this->excluder->addExclusionStrategy($strategy);
        $serializationData = new DefaultSerializationExclusionData(new stdClass(), new WriterContext());
        $deserializationData = new DefaultDeserializationExclusionData(new stdClass(), new ReaderContext());
        $this->excluder->applyPropertySerializationExclusionData($serializationData);
        $this->excluder->applyPropertyDeserializationExclusionData($deserializationData);
        self::assertTrue($strategy->calledSerialize);
        self::assertTrue($strategy->calledDeserialize);
    }

    public function testExcludePropertyDefaultModifiers(): void
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
            MockProvider::classMetadata(Foo::class, $this->propertyCollection)
        );

        self::assertTrue($this->excluder->excludePropertySerialize($property));
        self::assertTrue($this->excluder->excludePropertyDeserialize($property));
    }

    public function testExcludePrivateProperties(): void
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
            MockProvider::classMetadata(Foo::class, $this->propertyCollection)
        );

        self::assertTrue($this->excluder->excludePropertySerialize($property));
        self::assertTrue($this->excluder->excludePropertyDeserialize($property));
    }

    public function testExcludeProtectedProperties(): void
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
            MockProvider::classMetadata(Foo::class, $this->propertyCollection)
        );

        self::assertTrue($this->excluder->excludePropertySerialize($property));
        self::assertTrue($this->excluder->excludePropertyDeserialize($property));
    }

    public function testDoNotExcludeWithoutSinceUntilAnnotations(): void
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
            MockProvider::classMetadata(Foo::class, $this->propertyCollection)
        );

        self::assertFalse($this->excluder->excludePropertySerialize($property));
        self::assertFalse($this->excluder->excludePropertyDeserialize($property));
    }

    public function testDoNotExcludeWithoutVersion(): void
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
            MockProvider::classMetadata(Foo::class, $this->propertyCollection)
        );

        self::assertFalse($this->excluder->excludePropertySerialize($property));
        self::assertFalse($this->excluder->excludePropertyDeserialize($property));
    }

    public function testExcludeBeforeSince(): void
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
            MockProvider::classMetadata(Foo::class, $this->propertyCollection)
        );

        self::assertTrue($this->excluder->excludePropertySerialize($property));
        self::assertTrue($this->excluder->excludePropertyDeserialize($property));
    }

    public function testDoNotExcludeEqualToSince(): void
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
            MockProvider::classMetadata(Foo::class, $this->propertyCollection)
        );

        self::assertFalse($this->excluder->excludePropertySerialize($property));
        self::assertFalse($this->excluder->excludePropertyDeserialize($property));
    }

    public function testDoNotExcludeAfterSince(): void
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
            MockProvider::classMetadata(Foo::class, $this->propertyCollection)
        );

        self::assertFalse($this->excluder->excludePropertySerialize($property));
        self::assertFalse($this->excluder->excludePropertyDeserialize($property));
    }

    public function testDoNotExcludeBeforeUntil(): void
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
            MockProvider::classMetadata(Foo::class, $this->propertyCollection)
        );

        self::assertFalse($this->excluder->excludePropertySerialize($property));
        self::assertFalse($this->excluder->excludePropertyDeserialize($property));
    }

    public function testExcludeEqualToUntil(): void
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
            MockProvider::classMetadata(Foo::class, $this->propertyCollection)
        );

        self::assertTrue($this->excluder->excludePropertySerialize($property));
        self::assertTrue($this->excluder->excludePropertyDeserialize($property));
    }

    public function testExcludeGreaterThanUntil(): void
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
            MockProvider::classMetadata(Foo::class, $this->propertyCollection)
        );

        self::assertTrue($this->excluder->excludePropertySerialize($property));
        self::assertTrue($this->excluder->excludePropertyDeserialize($property));
    }

    public function testExcludeWithExcludeAnnotation(): void
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
            MockProvider::classMetadata(Foo::class, $this->propertyCollection)
        );

        self::assertTrue($this->excluder->excludePropertySerialize($property));
        self::assertTrue($this->excluder->excludePropertyDeserialize($property));
    }

    public function testExcludeWithClassExcludeAndPropertyExpose(): void
    {
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
            MockProvider::classMetadata(GsonMockChild::class, $this->propertyCollection)
        );

        self::assertFalse($this->excluder->excludePropertySerialize($property));
        self::assertFalse($this->excluder->excludePropertyDeserialize($property));
    }

    public function testExcludeWithClassExcludeAnnotation(): void
    {
        $annotations = new AnnotationCollection();

        $property = new Property(
            'foo',
            'foo',
            new TypeToken('string'),
            new GetByPublicProperty('foo'),
            new SetByPublicProperty('foo'),
            $annotations,
            ReflectionProperty::IS_PRIVATE,
            false,
            MockProvider::classMetadata(GsonMockChild::class, $this->propertyCollection)
        );

        self::assertTrue($this->excluder->excludePropertySerialize($property));
        self::assertTrue($this->excluder->excludePropertyDeserialize($property));
    }

    public function testExcludeWithExcludeAnnotationOnlySerialize(): void
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
            MockProvider::classMetadata(Foo::class, $this->propertyCollection)
        );

        self::assertTrue($this->excluder->excludePropertySerialize($property));
        self::assertFalse($this->excluder->excludePropertyDeserialize($property));
    }

    public function testExcludeWithExcludeAnnotationOnlyDeserialize(): void
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
            MockProvider::classMetadata(Foo::class, $this->propertyCollection)
        );

        self::assertFalse($this->excluder->excludePropertySerialize($property));
        self::assertTrue($this->excluder->excludePropertyDeserialize($property));
    }

    public function testDoNotExcludeWithExposeAnnotation(): void
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
            MockProvider::classMetadata(Foo::class, $this->propertyCollection)
        );

        self::assertFalse($this->excluder->excludePropertySerialize($property));
        self::assertFalse($this->excluder->excludePropertyDeserialize($property));
    }

    public function testDoNotExcludeWithExposeAnnotationOnlySerialize(): void
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
            MockProvider::classMetadata(Foo::class, $this->propertyCollection)
        );

        self::assertFalse($this->excluder->excludePropertySerialize($property));
        self::assertTrue($this->excluder->excludePropertyDeserialize($property));
    }

    public function testDoNotExcludeWithExposeAnnotationOnlyDeserialize(): void
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
            MockProvider::classMetadata(Foo::class, $this->propertyCollection)
        );

        self::assertTrue($this->excluder->excludePropertySerialize($property));
        self::assertFalse($this->excluder->excludePropertyDeserialize($property));
    }

    public function testDoNotExcludeWithExposeAnnotationWithoutRequireExpose(): void
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
            MockProvider::classMetadata(Foo::class, $this->propertyCollection)
        );

        self::assertFalse($this->excluder->excludePropertySerialize($property));
        self::assertFalse($this->excluder->excludePropertyDeserialize($property));
    }

    public function testExcludeWithoutExposeAnnotationWithRequireExpose(): void
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
            MockProvider::classMetadata(Foo::class, $this->propertyCollection)
        );

        self::assertTrue($this->excluder->excludePropertySerialize($property));
        self::assertTrue($this->excluder->excludePropertyDeserialize($property));
    }

    public function testExcludeFromStrategy(): void
    {
        $this->excluder->addExclusionStrategy(new BarPropertyExclusionStrategy());
        $this->excluder->addExclusionStrategy(new FooPropertyExclusionStrategy());

        $property = new Property(
            'foo',
            'foo',
            new TypeToken('string'),
            new GetByPublicProperty('foo'),
            new SetByPublicProperty('foo'),
            new AnnotationCollection(),
            ReflectionProperty::IS_PRIVATE,
            false,
            MockProvider::classMetadata(Foo::class, $this->propertyCollection)
        );

        self::assertTrue($this->excluder->excludePropertyBySerializationStrategy($property));
        self::assertTrue($this->excluder->excludePropertyByDeserializationStrategy($property));
    }

    public function testExcludeFromStrategyFalse(): void
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
            MockProvider::classMetadata(Foo::class, $this->propertyCollection)
        );

        self::assertFalse($this->excluder->excludePropertyBySerializationStrategy($property));
        self::assertFalse($this->excluder->excludePropertyByDeserializationStrategy($property));
    }
}
