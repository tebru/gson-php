<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal;

use PHPUnit_Framework_TestCase;
use ReflectionProperty;
use stdClass;
use Tebru\AnnotationReader\AnnotationCollection;
use Tebru\Gson\Annotation\Exclude;
use Tebru\Gson\Annotation\Expose;
use Tebru\Gson\Annotation\Since;
use Tebru\Gson\Annotation\Until;
use Tebru\Gson\Internal\AccessorStrategy\GetByPublicProperty;
use Tebru\Gson\Internal\AccessorStrategy\SetByPublicProperty;
use Tebru\Gson\Internal\Data\Property;
use Tebru\Gson\Internal\Data\PropertyCollection;
use Tebru\Gson\Internal\DefaultDeserializationExclusionData;
use Tebru\Gson\Internal\DefaultReaderContext;
use Tebru\Gson\Internal\DefaultSerializationExclusionData;
use Tebru\Gson\Internal\Excluder;
use Tebru\Gson\Internal\JsonDecodeReader;
use Tebru\Gson\Internal\JsonEncodeWriter;
use Tebru\Gson\Test\Mock\ExcluderExcludeSerializeMock;
use Tebru\Gson\Test\Mock\ExcluderExposeMock;
use Tebru\Gson\Test\Mock\ExcluderVersionMock;
use Tebru\Gson\Test\Mock\ExclusionStrategies\BarPropertyExclusionStrategy;
use Tebru\Gson\Test\Mock\ExclusionStrategies\ExcludeAllCacheableExclusionStrategy;
use Tebru\Gson\Test\Mock\ExclusionStrategies\FooDeserializationExclusionStrategy;
use Tebru\Gson\Test\Mock\ExclusionStrategies\FooPropertyExclusionStrategy;
use Tebru\Gson\Test\Mock\ExclusionStrategies\FooSerializationExclusionStrategy;
use Tebru\Gson\Test\Mock\Foo;
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

    public function testExcludeClassWithoutVersion()
    {
        $classMetadata = MockProvider::classMetadata(ExcluderVersionMock::class, $this->propertyCollection);

        self::assertFalse($this->excluder->excludeClassSerialize($classMetadata));
        self::assertFalse($this->excluder->excludeClassDeserialize($classMetadata));
    }

    public function testExcludeClassWithVersionLow()
    {
        $this->excluder->setVersion('0.1.0');
        $classMetadata = MockProvider::classMetadata(ExcluderVersionMock::class, $this->propertyCollection);
        
        self::assertTrue($this->excluder->excludeClassSerialize($classMetadata));
        self::assertTrue($this->excluder->excludeClassDeserialize($classMetadata));
    }

    public function testExcludeClassWithVersionEqualSince()
    {
        $this->excluder->setVersion('1');
        $classMetadata = MockProvider::classMetadata(ExcluderVersionMock::class, $this->propertyCollection);
        
        self::assertFalse($this->excluder->excludeClassSerialize($classMetadata));
        self::assertFalse($this->excluder->excludeClassDeserialize($classMetadata));
    }

    public function testExcludeClassWithVersionBetween()
    {
        $this->excluder->setVersion('1.5');
        $classMetadata = MockProvider::classMetadata(ExcluderVersionMock::class, $this->propertyCollection);

        self::assertFalse($this->excluder->excludeClassSerialize($classMetadata));
        self::assertFalse($this->excluder->excludeClassDeserialize($classMetadata));
    }

    public function testExcludeClassWithVersionEqualUntil()
    {
        $this->excluder->setVersion('2');
        $classMetadata = MockProvider::classMetadata(ExcluderVersionMock::class, $this->propertyCollection);

        self::assertTrue($this->excluder->excludeClassSerialize($classMetadata));
        self::assertTrue($this->excluder->excludeClassDeserialize($classMetadata));
    }

    public function testExcludeClassWithVersionHigh()
    {
        $this->excluder->setVersion('3');
        $classMetadata = MockProvider::classMetadata(ExcluderVersionMock::class, $this->propertyCollection);

        self::assertTrue($this->excluder->excludeClassSerialize($classMetadata));
        self::assertTrue($this->excluder->excludeClassDeserialize($classMetadata));
    }

    public function testExcludeClassWithExcludeAnnotation()
    {
        $classMetadata = MockProvider::classMetadata(ExcluderExcludeSerializeMock::class, $this->propertyCollection);

        self::assertTrue($this->excluder->excludeClassSerialize($classMetadata));
        self::assertFalse($this->excluder->excludeClassDeserialize($classMetadata));
    }

    public function testExcludeClassWithExposeAnnotation()
    {
        $this->excluder->setRequireExpose(true);
        $classMetadata = MockProvider::classMetadata(ExcluderExposeMock::class, $this->propertyCollection);

        self::assertFalse($this->excluder->excludeClassSerialize($classMetadata));
        self::assertTrue($this->excluder->excludeClassDeserialize($classMetadata));
    }

    public function testExcludeClassWithStrategySerialization()
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

    public function testExcludeClassWithStrategyDeserialization()
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

    public function testExcludeCacheable()
    {
        $this->excluder->addCachedExclusionStrategy(new ExcludeAllCacheableExclusionStrategy());
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

    public function testClassDataAware()
    {
        $strategy = new ExcludeAllCacheableExclusionStrategy();
        $this->excluder->addExclusionStrategy($strategy);
        $serializationData = new DefaultSerializationExclusionData(new stdClass(), new JsonEncodeWriter());
        $deserializationData = new DefaultDeserializationExclusionData(new stdClass(), new JsonDecodeReader('{}', new DefaultReaderContext()));
        $this->excluder->applyClassSerializationExclusionData($serializationData);
        $this->excluder->applyClassDeserializationExclusionData($deserializationData);
        self::assertTrue($strategy->calledSerialize);
        self::assertTrue($strategy->calledDeserialize);
    }

    public function testPropertyDataAware()
    {
        $strategy = new ExcludeAllCacheableExclusionStrategy();
        $this->excluder->addExclusionStrategy($strategy);
        $serializationData = new DefaultSerializationExclusionData(new stdClass(), new JsonEncodeWriter());
        $deserializationData = new DefaultDeserializationExclusionData(new stdClass(), new JsonDecodeReader('{}', new DefaultReaderContext()));
        $this->excluder->applyPropertySerializationExclusionData($serializationData);
        $this->excluder->applyPropertyDeserializationExclusionData($deserializationData);
        self::assertTrue($strategy->calledSerialize);
        self::assertTrue($strategy->calledDeserialize);
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
            MockProvider::classMetadata(Foo::class, $this->propertyCollection)
        );

        self::assertTrue($this->excluder->excludePropertySerialize($property));
        self::assertTrue($this->excluder->excludePropertyDeserialize($property));
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
            MockProvider::classMetadata(Foo::class, $this->propertyCollection)
        );

        self::assertTrue($this->excluder->excludePropertySerialize($property));
        self::assertTrue($this->excluder->excludePropertyDeserialize($property));
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
            MockProvider::classMetadata(Foo::class, $this->propertyCollection)
        );

        self::assertTrue($this->excluder->excludePropertySerialize($property));
        self::assertTrue($this->excluder->excludePropertyDeserialize($property));
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
            MockProvider::classMetadata(Foo::class, $this->propertyCollection)
        );

        self::assertFalse($this->excluder->excludePropertySerialize($property));
        self::assertFalse($this->excluder->excludePropertyDeserialize($property));
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
            MockProvider::classMetadata(Foo::class, $this->propertyCollection)
        );

        self::assertFalse($this->excluder->excludePropertySerialize($property));
        self::assertFalse($this->excluder->excludePropertyDeserialize($property));
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
            MockProvider::classMetadata(Foo::class, $this->propertyCollection)
        );

        self::assertTrue($this->excluder->excludePropertySerialize($property));
        self::assertTrue($this->excluder->excludePropertyDeserialize($property));
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
            MockProvider::classMetadata(Foo::class, $this->propertyCollection)
        );

        self::assertFalse($this->excluder->excludePropertySerialize($property));
        self::assertFalse($this->excluder->excludePropertyDeserialize($property));
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
            MockProvider::classMetadata(Foo::class, $this->propertyCollection)
        );

        self::assertFalse($this->excluder->excludePropertySerialize($property));
        self::assertFalse($this->excluder->excludePropertyDeserialize($property));
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
            MockProvider::classMetadata(Foo::class, $this->propertyCollection)
        );

        self::assertFalse($this->excluder->excludePropertySerialize($property));
        self::assertFalse($this->excluder->excludePropertyDeserialize($property));
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
            MockProvider::classMetadata(Foo::class, $this->propertyCollection)
        );

        self::assertTrue($this->excluder->excludePropertySerialize($property));
        self::assertTrue($this->excluder->excludePropertyDeserialize($property));
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
            MockProvider::classMetadata(Foo::class, $this->propertyCollection)
        );

        self::assertTrue($this->excluder->excludePropertySerialize($property));
        self::assertTrue($this->excluder->excludePropertyDeserialize($property));
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
            MockProvider::classMetadata(Foo::class, $this->propertyCollection)
        );

        self::assertTrue($this->excluder->excludePropertySerialize($property));
        self::assertTrue($this->excluder->excludePropertyDeserialize($property));
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
            MockProvider::classMetadata(Foo::class, $this->propertyCollection)
        );

        self::assertTrue($this->excluder->excludePropertySerialize($property));
        self::assertFalse($this->excluder->excludePropertyDeserialize($property));
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
            MockProvider::classMetadata(Foo::class, $this->propertyCollection)
        );

        self::assertFalse($this->excluder->excludePropertySerialize($property));
        self::assertTrue($this->excluder->excludePropertyDeserialize($property));
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
            MockProvider::classMetadata(Foo::class, $this->propertyCollection)
        );

        self::assertFalse($this->excluder->excludePropertySerialize($property));
        self::assertFalse($this->excluder->excludePropertyDeserialize($property));
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
            MockProvider::classMetadata(Foo::class, $this->propertyCollection)
        );

        self::assertFalse($this->excluder->excludePropertySerialize($property));
        self::assertTrue($this->excluder->excludePropertyDeserialize($property));
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
            MockProvider::classMetadata(Foo::class, $this->propertyCollection)
        );

        self::assertTrue($this->excluder->excludePropertySerialize($property));
        self::assertFalse($this->excluder->excludePropertyDeserialize($property));
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
            MockProvider::classMetadata(Foo::class, $this->propertyCollection)
        );

        self::assertFalse($this->excluder->excludePropertySerialize($property));
        self::assertFalse($this->excluder->excludePropertyDeserialize($property));
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
            MockProvider::classMetadata(Foo::class, $this->propertyCollection)
        );

        self::assertTrue($this->excluder->excludePropertySerialize($property));
        self::assertTrue($this->excluder->excludePropertyDeserialize($property));
    }

    public function testExcludeFromStrategy()
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
            MockProvider::classMetadata(Foo::class, $this->propertyCollection)
        );

        self::assertFalse($this->excluder->excludePropertyBySerializationStrategy($property));
        self::assertFalse($this->excluder->excludePropertyByDeserializationStrategy($property));
    }
}
