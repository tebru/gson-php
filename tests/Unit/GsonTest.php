<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit;

use DateTime;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use ReflectionProperty;
use Tebru\Gson\Context\ReaderContext;
use Tebru\Gson\Context\WriterContext;
use Tebru\Gson\Gson;
use Tebru\Gson\Internal\CacheProvider;
use Tebru\Gson\Internal\Naming\UpperCaseMethodNamingStrategy;
use Tebru\Gson\PropertyNamingPolicy;
use Tebru\Gson\Test\Mock\ChildClass;
use Tebru\Gson\Test\Mock\ExclusionStrategies\CacheableDataAwareExclusionStrategy;
use Tebru\Gson\Test\Mock\ExclusionStrategies\CacheableGsonMockExclusionStrategy;
use Tebru\Gson\Test\Mock\ExclusionStrategies\ExcludeAllExclusionStrategy;
use Tebru\Gson\Test\Mock\ExclusionStrategies\GsonMockMetadataVisitor;
use Tebru\Gson\Test\Mock\GsonMockChild;
use Tebru\Gson\Test\Mock\GsonMockResponse;
use Tebru\Gson\Test\Mock\GsonObjectMock;
use Tebru\Gson\Test\Mock\GsonMock;
use Tebru\Gson\Test\Mock\GsonObjectMockable;
use Tebru\Gson\Test\Mock\GsonObjectMockInstanceCreatorMock;
use Tebru\Gson\Test\Mock\Strategy\TwoPropertyNamingStrategy;
use Tebru\Gson\Test\Mock\TypeAdapter\CustomTypeAdapter;
use Tebru\Gson\Test\Mock\TypeAdapter\GsonObjectMockTypeAdapterMock;
use Tebru\Gson\Test\Mock\TypeAdapter\Integer1Deserializer;
use Tebru\Gson\Test\Mock\TypeAdapter\Integer1Serializer;
use Tebru\Gson\Test\Mock\TypeAdapter\Integer1SerializerDeserializer;
use Tebru\Gson\Test\Mock\TypeAdapter\Integer1TypeAdapter;
use Tebru\Gson\Test\Mock\TypeAdapter\Integer1TypeAdapterFactory;

/**
 * Class GsonTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\ObjectConstructorAwareTrait
 * @covers \Tebru\Gson\Gson
 * @covers \Tebru\Gson\GsonBuilder
 */
class GsonTest extends TestCase
{
    public function testSimpleDeserialize(): void
    {
        $gson = Gson::builder()->build();

        $gsonMock = $gson->fromJson($this->json(), GsonMock::class);

        self::assertSame(1, $gsonMock->getInteger());
        self::assertSame(3.2, $gsonMock->getFloat());
        self::assertSame('foo', $gsonMock->getString());
        self::assertFalse($gsonMock->getBoolean());
        self::assertSame(['foo' => 'bar'], $gsonMock->getArray());
        self::assertSame('2017-01-01T12:01:23-06:00', $gsonMock->getDate()->format(DateTime::ATOM));
        self::assertSame('public', $gsonMock->public);
        self::assertAttributeSame('protected', 'protected', $gsonMock);
        self::assertSame('since', $gsonMock->getSince());
        self::assertSame('until', $gsonMock->getUntil());
        self::assertSame('accessor', $gsonMock->getMyAccessor());
        self::assertSame('serializedname', $gsonMock->getSerializedname());
        self::assertSame([1, 2, 3], $gsonMock->getType());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getJsonAdapter());
        self::assertFalse($gsonMock->getExpose());
        self::assertNull($gsonMock->getExclude());
        self::assertTrue($gsonMock->getExcludeFromStrategy());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getGsonObjectMock());
    }

    public function testDeserializeNotSince(): void
    {
        $gson = Gson::builder()
            ->setVersion(1)
            ->build();

        $gsonMock = $gson->fromJson($this->json(), GsonMock::class);

        self::assertSame(1, $gsonMock->getInteger());
        self::assertSame(3.2, $gsonMock->getFloat());
        self::assertSame('foo', $gsonMock->getString());
        self::assertFalse($gsonMock->getBoolean());
        self::assertSame(['foo' => 'bar'], $gsonMock->getArray());
        self::assertSame('2017-01-01T12:01:23-06:00', $gsonMock->getDate()->format(DateTime::ATOM));
        self::assertSame('public', $gsonMock->public);
        self::assertAttributeSame('protected', 'protected', $gsonMock);
        self::assertNull($gsonMock->getSince());
        self::assertSame('until', $gsonMock->getUntil());
        self::assertSame('accessor', $gsonMock->getMyAccessor());
        self::assertSame('serializedname', $gsonMock->getSerializedname());
        self::assertSame([1, 2, 3], $gsonMock->getType());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getJsonAdapter());
        self::assertFalse($gsonMock->getExpose());
        self::assertNull($gsonMock->getExclude());
        self::assertTrue($gsonMock->getExcludeFromStrategy());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getGsonObjectMock());
    }

    public function testDeserializeNotUntil(): void
    {
        $gson = Gson::builder()
            ->setVersion(2)
            ->build();

        $gsonMock = $gson->fromJson($this->json(), GsonMock::class);

        self::assertSame(1, $gsonMock->getInteger());
        self::assertSame(3.2, $gsonMock->getFloat());
        self::assertSame('foo', $gsonMock->getString());
        self::assertFalse($gsonMock->getBoolean());
        self::assertSame(['foo' => 'bar'], $gsonMock->getArray());
        self::assertSame('2017-01-01T12:01:23-06:00', $gsonMock->getDate()->format(DateTime::ATOM));
        self::assertSame('public', $gsonMock->public);
        self::assertAttributeSame('protected', 'protected', $gsonMock);
        self::assertSame('since', $gsonMock->getSince());
        self::assertNull($gsonMock->getUntil());
        self::assertSame('accessor', $gsonMock->getMyAccessor());
        self::assertSame('serializedname', $gsonMock->getSerializedname());
        self::assertSame([1, 2, 3], $gsonMock->getType());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getJsonAdapter());
        self::assertFalse($gsonMock->getExpose());
        self::assertNull($gsonMock->getExclude());
        self::assertTrue($gsonMock->getExcludeFromStrategy());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getGsonObjectMock());
    }

    public function testDeserializeNoProtected(): void
    {
        $gson = Gson::builder()
            ->setExcludedModifier(ReflectionProperty::IS_PROTECTED)
            ->build();

        $gsonMock = $gson->fromJson($this->json(), GsonMock::class);

        self::assertSame(1, $gsonMock->getInteger());
        self::assertSame(3.2, $gsonMock->getFloat());
        self::assertSame('foo', $gsonMock->getString());
        self::assertFalse($gsonMock->getBoolean());
        self::assertSame(['foo' => 'bar'], $gsonMock->getArray());
        self::assertSame('2017-01-01T12:01:23-06:00', $gsonMock->getDate()->format(DateTime::ATOM));
        self::assertSame('public', $gsonMock->public);
        self::assertAttributeSame(null, 'protected', $gsonMock);
        self::assertSame('since', $gsonMock->getSince());
        self::assertSame('until', $gsonMock->getUntil());
        self::assertSame('accessor', $gsonMock->getMyAccessor());
        self::assertSame('serializedname', $gsonMock->getSerializedname());
        self::assertSame([1, 2, 3], $gsonMock->getType());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getJsonAdapter());
        self::assertFalse($gsonMock->getExpose());
        self::assertNull($gsonMock->getExclude());
        self::assertTrue($gsonMock->getExcludeFromStrategy());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getGsonObjectMock());
    }

    public function testDeserializeRequireExpose(): void
    {
        $gson = Gson::builder()
            ->requireExposeAnnotation()
            ->build();

        $gsonMock = $gson->fromJson($this->json(), GsonMock::class);

        self::assertNull($gsonMock->getInteger());
        self::assertNull($gsonMock->getFloat());
        self::assertNull($gsonMock->getString());
        self::assertNull($gsonMock->getBoolean());
        self::assertNull($gsonMock->getArray());
        self::assertNull($gsonMock->getDate());
        self::assertNull($gsonMock->public);
        self::assertAttributeSame(null, 'protected', $gsonMock);
        self::assertNull($gsonMock->getSince());
        self::assertNull($gsonMock->getUntil());
        self::assertNull($gsonMock->getMyAccessor());
        self::assertNull($gsonMock->getSerializedname());
        self::assertNull($gsonMock->getType());
        self::assertNull($gsonMock->getJsonAdapter());
        self::assertFalse($gsonMock->getExpose());
        self::assertNull($gsonMock->getExclude());
        self::assertNull($gsonMock->getExcludeFromStrategy());
        self::assertEquals(null, $gsonMock->getGsonObjectMock());
    }

    public function testDeserializeCustomTypeAdapter(): void
    {
        $gson = Gson::builder()
            ->registerType('int', new Integer1TypeAdapter())
            ->setEnableScalarAdapters(true)
            ->build();

        $gsonMock = $gson->fromJson($this->json(), GsonMock::class);

        self::assertSame(2, $gsonMock->getInteger());
        self::assertSame(3.2, $gsonMock->getFloat());
        self::assertSame('foo', $gsonMock->getString());
        self::assertFalse($gsonMock->getBoolean());
        self::assertSame(['foo' => 'bar'], $gsonMock->getArray());
        self::assertSame('2017-01-01T12:01:23-06:00', $gsonMock->getDate()->format(DateTime::ATOM));
        self::assertSame('public', $gsonMock->public);
        self::assertAttributeSame('protected', 'protected', $gsonMock);
        self::assertSame('since', $gsonMock->getSince());
        self::assertSame('until', $gsonMock->getUntil());
        self::assertSame('accessor', $gsonMock->getMyAccessor());
        self::assertSame('serializedname', $gsonMock->getSerializedname());
        self::assertSame([2, 3, 4], $gsonMock->getType());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getJsonAdapter());
        self::assertFalse($gsonMock->getExpose());
        self::assertNull($gsonMock->getExclude());
        self::assertTrue($gsonMock->getExcludeFromStrategy());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getGsonObjectMock());
    }

    public function testDeserializeCustomTypeInterface(): void
    {
        $gson = Gson::builder()
            ->registerType(GsonObjectMockable::class, new GsonObjectMockTypeAdapterMock())
            ->build();

        $gsonMock = $gson->fromJson($this->json(), GsonMock::class);

        self::assertSame(1, $gsonMock->getInteger());
        self::assertSame(3.2, $gsonMock->getFloat());
        self::assertSame('foo', $gsonMock->getString());
        self::assertFalse($gsonMock->getBoolean());
        self::assertSame(['foo' => 'bar'], $gsonMock->getArray());
        self::assertSame('2017-01-01T12:01:23-06:00', $gsonMock->getDate()->format(DateTime::ATOM));
        self::assertSame('public', $gsonMock->public);
        self::assertAttributeSame('protected', 'protected', $gsonMock);
        self::assertSame('since', $gsonMock->getSince());
        self::assertSame('until', $gsonMock->getUntil());
        self::assertSame('accessor', $gsonMock->getMyAccessor());
        self::assertSame('serializedname', $gsonMock->getSerializedname());
        self::assertSame([1, 2, 3], $gsonMock->getType());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getJsonAdapter());
        self::assertFalse($gsonMock->getExpose());
        self::assertNull($gsonMock->getExclude());
        self::assertTrue($gsonMock->getExcludeFromStrategy());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getGsonObjectMock());
    }

    public function testDeserializeCustomTypeAdapterFactory(): void
    {
        $gson = Gson::builder()
            ->addTypeAdapterFactory(new Integer1TypeAdapterFactory())
            ->setEnableScalarAdapters(true)
            ->build();

        $gsonMock = $gson->fromJson($this->json(), GsonMock::class);

        self::assertSame(2, $gsonMock->getInteger());
        self::assertSame(3.2, $gsonMock->getFloat());
        self::assertSame('foo', $gsonMock->getString());
        self::assertFalse($gsonMock->getBoolean());
        self::assertSame(['foo' => 'bar'], $gsonMock->getArray());
        self::assertSame('2017-01-01T12:01:23-06:00', $gsonMock->getDate()->format(DateTime::ATOM));
        self::assertSame('public', $gsonMock->public);
        self::assertAttributeSame('protected', 'protected', $gsonMock);
        self::assertSame('since', $gsonMock->getSince());
        self::assertSame('until', $gsonMock->getUntil());
        self::assertSame('accessor', $gsonMock->getMyAccessor());
        self::assertSame('serializedname', $gsonMock->getSerializedname());
        self::assertSame([2, 3, 4], $gsonMock->getType());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getJsonAdapter());
        self::assertFalse($gsonMock->getExpose());
        self::assertNull($gsonMock->getExclude());
        self::assertTrue($gsonMock->getExcludeFromStrategy());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getGsonObjectMock());
    }

    public function testDeserializeCustomDeserializer(): void
    {
        $gson = Gson::builder()
            ->registerType(GsonMock::class, new Integer1Deserializer())
            ->build();

        $gsonMock = $gson->fromJson($this->json(), GsonMock::class);

        self::assertSame(2, $gsonMock->getInteger());
        self::assertSame(3.2, $gsonMock->getFloat());
        self::assertSame('foo', $gsonMock->getString());
        self::assertFalse($gsonMock->getBoolean());
        self::assertSame(['foo' => 'bar'], $gsonMock->getArray());
        self::assertSame('2017-01-01T12:01:23-06:00', $gsonMock->getDate()->format(DateTime::ATOM));
        self::assertSame('public', $gsonMock->public);
        self::assertAttributeSame(null, 'protected', $gsonMock);
        self::assertSame('since', $gsonMock->getSince());
        self::assertSame('until', $gsonMock->getUntil());
        self::assertSame('accessor', $gsonMock->getMyAccessor());
        self::assertSame('serializedname', $gsonMock->getSerializedname());
        self::assertSame([2, 3, 4], $gsonMock->getType());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getJsonAdapter());
        self::assertFalse($gsonMock->getExpose());
        self::assertNull($gsonMock->getExclude());
        self::assertTrue($gsonMock->getExcludeFromStrategy());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getGsonObjectMock());
    }

    public function testDeserializeCustomDeserializerBoth(): void
    {
        $gson = Gson::builder()
            ->registerType(GsonMock::class, new Integer1SerializerDeserializer())
            ->build();

        $gsonMock = $gson->fromJson($this->json(), GsonMock::class);

        self::assertSame(2, $gsonMock->getInteger());
        self::assertSame(3.2, $gsonMock->getFloat());
        self::assertSame('foo', $gsonMock->getString());
        self::assertFalse($gsonMock->getBoolean());
        self::assertSame(['foo' => 'bar'], $gsonMock->getArray());
        self::assertSame('2017-01-01T12:01:23-06:00', $gsonMock->getDate()->format(DateTime::ATOM));
        self::assertSame('public', $gsonMock->public);
        self::assertAttributeSame(null, 'protected', $gsonMock);
        self::assertSame('since', $gsonMock->getSince());
        self::assertSame('until', $gsonMock->getUntil());
        self::assertSame('accessor', $gsonMock->getMyAccessor());
        self::assertSame('serializedname', $gsonMock->getSerializedname());
        self::assertSame([2, 3, 4], $gsonMock->getType());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getJsonAdapter());
        self::assertFalse($gsonMock->getExpose());
        self::assertNull($gsonMock->getExclude());
        self::assertTrue($gsonMock->getExcludeFromStrategy());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getGsonObjectMock());
    }

    public function testDeserializeUsingInstanceCreator(): void
    {
        $gson = Gson::builder()
            ->addInstanceCreator(GsonObjectMock::class, new GsonObjectMockInstanceCreatorMock())
            ->build();

        $gsonMock = $gson->fromJson($this->json(), GsonMock::class);

        self::assertSame(1, $gsonMock->getInteger());
        self::assertSame(3.2, $gsonMock->getFloat());
        self::assertSame('foo', $gsonMock->getString());
        self::assertFalse($gsonMock->getBoolean());
        self::assertSame(['foo' => 'bar'], $gsonMock->getArray());
        self::assertSame('2017-01-01T12:01:23-06:00', $gsonMock->getDate()->format(DateTime::ATOM));
        self::assertSame('public', $gsonMock->public);
        self::assertAttributeSame('protected', 'protected', $gsonMock);
        self::assertSame('since', $gsonMock->getSince());
        self::assertSame('until', $gsonMock->getUntil());
        self::assertSame('accessor', $gsonMock->getMyAccessor());
        self::assertSame('serializedname', $gsonMock->getSerializedname());
        self::assertSame([1, 2, 3], $gsonMock->getType());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getJsonAdapter());
        self::assertFalse($gsonMock->getExpose());
        self::assertNull($gsonMock->getExclude());
        self::assertTrue($gsonMock->getExcludeFromStrategy());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getGsonObjectMock());
    }

    public function testDeserializeUsingInstanceCreatorInterface(): void
    {
        $gson = Gson::builder()
            ->addInstanceCreator(GsonObjectMockable::class, new GsonObjectMockInstanceCreatorMock())
            ->build();

        $gsonMock = $gson->fromJson($this->json(), GsonMock::class);

        self::assertSame(1, $gsonMock->getInteger());
        self::assertSame(3.2, $gsonMock->getFloat());
        self::assertSame('foo', $gsonMock->getString());
        self::assertFalse($gsonMock->getBoolean());
        self::assertSame(['foo' => 'bar'], $gsonMock->getArray());
        self::assertSame('2017-01-01T12:01:23-06:00', $gsonMock->getDate()->format(DateTime::ATOM));
        self::assertSame('public', $gsonMock->public);
        self::assertAttributeSame('protected', 'protected', $gsonMock);
        self::assertSame('since', $gsonMock->getSince());
        self::assertSame('until', $gsonMock->getUntil());
        self::assertSame('accessor', $gsonMock->getMyAccessor());
        self::assertSame('serializedname', $gsonMock->getSerializedname());
        self::assertSame([1, 2, 3], $gsonMock->getType());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getJsonAdapter());
        self::assertFalse($gsonMock->getExpose());
        self::assertNull($gsonMock->getExclude());
        self::assertTrue($gsonMock->getExcludeFromStrategy());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getGsonObjectMock());
    }

    public function testDeserializeWithVisitor(): void
    {
        $gson = Gson::builder()
            ->addClassMetadataVisitor(new GsonMockMetadataVisitor())
            ->requireExclusionCheckAnnotation()
            ->build();

        $gsonMock = $gson->fromJson($this->json(), GsonMock::class);

        self::assertSame(1, $gsonMock->getInteger());
        self::assertSame(3.2, $gsonMock->getFloat());
        self::assertSame('foo', $gsonMock->getString());
        self::assertFalse($gsonMock->getBoolean());
        self::assertSame(['foo' => 'bar'], $gsonMock->getArray());
        self::assertSame('2017-01-01T12:01:23-06:00', $gsonMock->getDate()->format(DateTime::ATOM));
        self::assertSame('public', $gsonMock->public);
        self::assertAttributeSame('protected', 'protected', $gsonMock);
        self::assertSame('since', $gsonMock->getSince());
        self::assertSame('until', $gsonMock->getUntil());
        self::assertSame('accessor', $gsonMock->getMyAccessor());
        self::assertSame('serializedname', $gsonMock->getSerializedname());
        self::assertSame([1, 2, 3], $gsonMock->getType());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getJsonAdapter());
        self::assertFalse($gsonMock->getExpose());
        self::assertNull($gsonMock->getExclude());
        self::assertNull($gsonMock->getExcludeFromStrategy());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getGsonObjectMock());
    }

    public function testDeserializeWithCachedExclusionStrategy(): void
    {
        $gson = Gson::builder()
            ->addExclusion(new CacheableGsonMockExclusionStrategy())
            ->build();

        $gsonMock = $gson->fromJson($this->json(), GsonMock::class);

        self::assertSame(1, $gsonMock->getInteger());
        self::assertSame(3.2, $gsonMock->getFloat());
        self::assertSame('foo', $gsonMock->getString());
        self::assertFalse($gsonMock->getBoolean());
        self::assertSame(['foo' => 'bar'], $gsonMock->getArray());
        self::assertSame('2017-01-01T12:01:23-06:00', $gsonMock->getDate()->format(DateTime::ATOM));
        self::assertSame('public', $gsonMock->public);
        self::assertAttributeSame('protected', 'protected', $gsonMock);
        self::assertSame('since', $gsonMock->getSince());
        self::assertSame('until', $gsonMock->getUntil());
        self::assertSame('accessor', $gsonMock->getMyAccessor());
        self::assertSame('serializedname', $gsonMock->getSerializedname());
        self::assertSame([1, 2, 3], $gsonMock->getType());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getJsonAdapter());
        self::assertFalse($gsonMock->getExpose());
        self::assertNull($gsonMock->getExclude());
        self::assertNull($gsonMock->getExcludeFromStrategy());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getGsonObjectMock());
    }

    public function testDeserializeExclusion(): void
    {
        $gson = Gson::builder()->addExclusion(new ExcludeAllExclusionStrategy())->build();
        $gsonMock = $gson->fromJson($this->json(), GsonMock::class);

        self::assertNull($gsonMock);
    }

    public function testCachedDataAwareExclusionStrategy(): void
    {
        try {
            Gson::builder()->addExclusion(new CacheableDataAwareExclusionStrategy());
        } catch (LogicException $exception) {
            self::assertSame('Gson: Cacheable exclusion strategies must not implement *DataAware interfaces', $exception->getMessage());
            return;
        }
        self::fail('Exception not thrown');
    }

    public function testDeserializeWithPropertyNamingPolicy(): void
    {
        $gson = Gson::builder()
            ->setPropertyNamingPolicy(PropertyNamingPolicy::IDENTITY)
            ->build();

        $array = [
            'integer' => 1,
            'float' => 3.2,
            'string' => 'foo',
            'boolean' => false,
            'array' => ['foo' => 'bar'],
            'date' => '2017-01-01T12:01:23-06:00',
            'public' => 'public',
            'protected' => 'protected',
            'since' => 'since',
            'until' => 'until',
            'accessor' => 'accessor',
            'serialized_name' => 'serializedname',
            'type' => [1, 2, 3],
            'jsonAdapter' => 'bar',
            'expose' => false,
            'exclude' => true,
            'excludeFromStrategy' => true,
            'gsonObjectMock' => ['foo' => 'bar'],
        ];

        $json = json_encode($array);

        $gsonMock = $gson->fromJson($json, GsonMock::class);

        self::assertSame(1, $gsonMock->getInteger());
        self::assertSame(3.2, $gsonMock->getFloat());
        self::assertSame('foo', $gsonMock->getString());
        self::assertFalse($gsonMock->getBoolean());
        self::assertSame(['foo' => 'bar'], $gsonMock->getArray());
        self::assertSame('2017-01-01T12:01:23-06:00', $gsonMock->getDate()->format(DateTime::ATOM));
        self::assertSame('public', $gsonMock->public);
        self::assertAttributeSame('protected', 'protected', $gsonMock);
        self::assertSame('since', $gsonMock->getSince());
        self::assertSame('until', $gsonMock->getUntil());
        self::assertSame('accessor', $gsonMock->getMyAccessor());
        self::assertSame('serializedname', $gsonMock->getSerializedname());
        self::assertSame([1, 2, 3], $gsonMock->getType());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getJsonAdapter());
        self::assertFalse($gsonMock->getExpose());
        self::assertNull($gsonMock->getExclude());
        self::assertTrue($gsonMock->getExcludeFromStrategy());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getGsonObjectMock());
    }

    public function testDeserializeWithPropertyNamingStrategy(): void
    {
        $gson = Gson::builder()
            ->setPropertyNamingStrategy(new TwoPropertyNamingStrategy())
            ->build();

        $array = [
            'integer2' => 1,
            'float2' => 3.2,
            'string2' => 'foo',
            'boolean2' => false,
            'array2' => ['foo' => 'bar'],
            'date2' => '2017-01-01T12:01:23-06:00',
            'public2' => 'public',
            'protected2' => 'protected',
            'since2' => 'since',
            'until2' => 'until',
            'accessor2' => 'accessor',
            'serialized_name' => 'serializedname',
            'type2' => [1, 2, 3],
            'jsonAdapter2' => 'bar',
            'expose2' => false,
            'exclude2' => true,
            'excludeFromStrategy2' => true,
            'gsonObjectMock2' => ['foo2' => 'bar'],
        ];

        $json = json_encode($array);

        $gsonMock = $gson->fromJson($json, GsonMock::class);

        self::assertSame(1, $gsonMock->getInteger());
        self::assertSame(3.2, $gsonMock->getFloat());
        self::assertSame('foo', $gsonMock->getString());
        self::assertFalse($gsonMock->getBoolean());
        self::assertSame(['foo' => 'bar'], $gsonMock->getArray());
        self::assertSame('2017-01-01T12:01:23-06:00', $gsonMock->getDate()->format(DateTime::ATOM));
        self::assertSame('public', $gsonMock->public);
        self::assertAttributeSame('protected', 'protected', $gsonMock);
        self::assertSame('since', $gsonMock->getSince());
        self::assertSame('until', $gsonMock->getUntil());
        self::assertSame('accessor', $gsonMock->getMyAccessor());
        self::assertSame('serializedname', $gsonMock->getSerializedname());
        self::assertSame([1, 2, 3], $gsonMock->getType());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getJsonAdapter());
        self::assertFalse($gsonMock->getExpose());
        self::assertNull($gsonMock->getExclude());
        self::assertTrue($gsonMock->getExcludeFromStrategy());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getGsonObjectMock());
    }

    public function testDeserializeWithMethodNamingStrategy(): void
    {
        $gson = Gson::builder()
            ->setMethodNamingStrategy(new UpperCaseMethodNamingStrategy())
            ->build();

        $gsonMock = $gson->fromJson($this->json(), GsonMock::class);

        self::assertSame(1, $gsonMock->getInteger());
        self::assertSame(3.2, $gsonMock->getFloat());
        self::assertSame('foo', $gsonMock->getString());
        self::assertFalse($gsonMock->getBoolean());
        self::assertSame(['foo' => 'bar'], $gsonMock->getArray());
        self::assertSame('2017-01-01T12:01:23-06:00', $gsonMock->getDate()->format(DateTime::ATOM));
        self::assertSame('public', $gsonMock->public);
        self::assertAttributeSame('protected', 'protected', $gsonMock);
        self::assertSame('since', $gsonMock->getSince());
        self::assertSame('until', $gsonMock->getUntil());
        self::assertSame('accessor', $gsonMock->getMyAccessor());
        self::assertSame('serializedname', $gsonMock->getSerializedname());
        self::assertSame([1, 2, 3], $gsonMock->getType());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getJsonAdapter());
        self::assertFalse($gsonMock->getExpose());
        self::assertNull($gsonMock->getExclude());
        self::assertTrue($gsonMock->getExcludeFromStrategy());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getGsonObjectMock());
    }


    public function testDeserializeUsesSameObject(): void
    {
        $gsonMock = new GsonMock();
        $gsonMock->setExclude(false);

        $gson = Gson::builder()->build();

        $returnedObject = $gson->fromJson($this->json(), $gsonMock);

        self::assertSame($gsonMock, $returnedObject);
    }

    public function testDeserializeUsesSameObjectNested(): void
    {
        $gsonMock = new GsonMock();
        $gsonMock->setExclude(false);
        $gsonMock->setGsonObjectMock(new GsonObjectMock('test'));

        $gson = Gson::builder()->build();

        $returnedObject = $gson->fromJson($this->json(), $gsonMock);

        self::assertSame($gsonMock, $returnedObject);
    }

    public function testSimpleDeserializeArray(): void
    {
        $gson = Gson::builder()->build();

        $gsonMock = $gson->fromNormalized(json_decode($this->json(), true), GsonMock::class);

        self::assertSame(1, $gsonMock->getInteger());
        self::assertSame(3.2, $gsonMock->getFloat());
        self::assertSame('foo', $gsonMock->getString());
        self::assertFalse($gsonMock->getBoolean());
        self::assertSame(['foo' => 'bar'], $gsonMock->getArray());
        self::assertSame('2017-01-01T12:01:23-06:00', $gsonMock->getDate()->format(DateTime::ATOM));
        self::assertSame('public', $gsonMock->public);
        self::assertAttributeSame('protected', 'protected', $gsonMock);
        self::assertSame('since', $gsonMock->getSince());
        self::assertSame('until', $gsonMock->getUntil());
        self::assertSame('accessor', $gsonMock->getMyAccessor());
        self::assertSame('serializedname', $gsonMock->getSerializedname());
        self::assertSame([1, 2, 3], $gsonMock->getType());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getJsonAdapter());
        self::assertFalse($gsonMock->getExpose());
        self::assertNull($gsonMock->getExclude());
        self::assertTrue($gsonMock->getExcludeFromStrategy());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getGsonObjectMock());
    }

    public function testDeserializeInteger(): void
    {
        $gson = Gson::builder()
            ->addTypeAdapterFactory(new CustomTypeAdapter())
            ->setEnableScalarAdapters(false)
            ->build();
        $result = $gson->fromJson('1', '');

        self::assertSame(1, $result);
    }

    public function testDeserializeChildExclude(): void
    {
        $gson = Gson::builder()->build();

        $jsonArray = json_decode($this->json(), true);
        $jsonArray['id'] = 1;
        $jsonArray['excluded'] = true;

        $gsonMock = $gson->fromJson(json_encode($jsonArray), GsonMockChild::class);

        self::assertSame(1, $gsonMock->id);
        self::assertNull($gsonMock->excluded);
        self::assertNull($gsonMock->getInteger());
        self::assertNull($gsonMock->getFloat());
        self::assertNull($gsonMock->getString());
        self::assertNull($gsonMock->getBoolean());
        self::assertNull($gsonMock->getArray());
        self::assertNull($gsonMock->getDate());
        self::assertNull($gsonMock->public);
        self::assertNull($gsonMock->getSince());
        self::assertNull($gsonMock->getUntil());
        self::assertNull($gsonMock->getMyAccessor());
        self::assertNull($gsonMock->getSerializedname());
        self::assertNull($gsonMock->getType());
        self::assertNull($gsonMock->getJsonAdapter());
        self::assertFalse($gsonMock->getExpose());
        self::assertNull($gsonMock->getExclude());
        self::assertNull($gsonMock->getExcludeFromStrategy());
        self::assertNull($gsonMock->getGsonObjectMock());
    }

    public function testSerializeSimple(): void
    {
        $gson = Gson::builder()
            ->addTypeAdapterFactory(new CustomTypeAdapter())
            ->build();
        $result = $gson->toJson($this->gsonMock());
        $json = json_decode($this->json(), true);
        $json['virtual'] = 2;
        unset($json['exclude']);

        self::assertJsonStringEqualsJsonString(json_encode($json), $result);
    }

    public function testSerializeOverride(): void
    {
        $gson = Gson::builder()
            ->addTypeAdapterFactory(new CustomTypeAdapter())
            ->build();
        $result = $gson->toJson($this->gsonMock(GsonMockResponse::class), GsonMock::class);
        $json = json_decode($this->json(), true);
        $json['virtual'] = 2;
        unset($json['exclude']);

        self::assertJsonStringEqualsJsonString(json_encode($json), $result);
    }

    public function testSerializeDateTimeFormat(): void
    {
        $gson = Gson::builder()
            ->addTypeAdapterFactory(new CustomTypeAdapter())
            ->setDateTimeFormat('Y')
            ->build();
        $result = $gson->toJson($this->gsonMock());
        $json = json_decode($this->json(), true);
        $json['virtual'] = 2;
        $json['date'] = '2017';
        unset($json['exclude']);

        self::assertJsonStringEqualsJsonString(json_encode($json), $result);
    }

    public function testSerializeNulls(): void
    {
        $context = new WriterContext();
        $context->setSerializeNull(true);

        $gson = Gson::builder()
            ->addTypeAdapterFactory(new CustomTypeAdapter())
            ->setWriterContext($context)
            ->build();
        $result = $gson->toJson(new GsonMock());

        $expected = '{
            "integer": null,
            "float": null,
            "string": null,
            "boolean": null,
            "array": null,
            "date": null,
            "public": null,
            "protected": null,
            "since": null,
            "until": null,
            "accessor": null,
            "serialized_name": null,
            "type": null,
            "json_adapter": null,
            "expose": null,
            "exclude_from_strategy": null,
            "gson_object_mock": null,
            "virtual": 2,
            "excluded_class": null,
            "pseudo_class": null
        }';

        self::assertJsonStringEqualsJsonString($expected, $result);
    }

    public function testSerializeNotSince(): void
    {
        $gson = Gson::builder()
            ->addTypeAdapterFactory(new CustomTypeAdapter())
            ->setVersion(1)
            ->build();

        $result = $gson->toJson($this->gsonMock());
        $json = json_decode($this->json(), true);
        $json['virtual'] = 2;
        unset($json['exclude'], $json['since']);

        self::assertJsonStringEqualsJsonString(json_encode($json), $result);
    }

    public function testSerializeNotUntil(): void
    {
        $gson = Gson::builder()
            ->addTypeAdapterFactory(new CustomTypeAdapter())
            ->setVersion(2)
            ->build();

        $result = $gson->toJson($this->gsonMock());
        $json = json_decode($this->json(), true);
        $json['virtual'] = 2;
        unset($json['exclude'], $json['until']);

        self::assertJsonStringEqualsJsonString(json_encode($json), $result);
    }

    public function testSerializeNotProtected(): void
    {
        $gson = Gson::builder()
            ->addTypeAdapterFactory(new CustomTypeAdapter())
            ->setExcludedModifier(ReflectionProperty::IS_PROTECTED)
            ->build();

        $result = $gson->toJson($this->gsonMock());
        $json = json_decode($this->json(), true);
        $json['virtual'] = 2;
        unset($json['exclude'], $json['protected']);

        self::assertJsonStringEqualsJsonString(json_encode($json), $result);
    }

    public function testSerializeRequireExpose(): void
    {
        $gson = Gson::builder()
            ->requireExposeAnnotation()
            ->build();

        $result = $gson->toJson($this->gsonMock());

        self::assertJsonStringEqualsJsonString('{"expose": false}', $result);
    }

    public function testSerializeCustomTypeAdapter(): void
    {
        $gson = Gson::builder()
            ->addTypeAdapterFactory(new CustomTypeAdapter())
            ->registerType('int', new Integer1TypeAdapter())
            ->setEnableScalarAdapters(true)
            ->build();

        $result = $gson->toJson($this->gsonMock());
        $json = json_decode($this->json(), true);
        unset($json['exclude']);
        $json['virtual'] = 3;
        $json['integer'] = 2;
        $json['type'] = [2, 3, 4];

        self::assertJsonStringEqualsJsonString(json_encode($json), $result);
    }

    public function testSerializeCustomTypeAdapterFactory(): void
    {
        $gson = Gson::builder()
            ->addTypeAdapterFactory(new CustomTypeAdapter())
            ->addTypeAdapterFactory(new Integer1TypeAdapterFactory())
            ->setEnableScalarAdapters(true)
            ->build();

        $result = $gson->toJson($this->gsonMock());
        $json = json_decode($this->json(), true);
        $json['virtual'] = 3;
        unset($json['exclude']);
        $json['integer'] = 2;
        $json['type'] = [2, 3, 4];

        self::assertJsonStringEqualsJsonString(json_encode($json), $result);
    }

    public function testSerializeCustomSerializer(): void
    {
        $gson = Gson::builder()
            ->registerType(GsonMock::class, new Integer1Serializer())
            ->build();

        $result = $gson->toJson($this->gsonMock());
        $json = json_decode($this->json(), true);
        unset($json['exclude'], $json['protected']);
        $json['integer'] = 2;
        $json['type'] = [2, 3, 4];

        self::assertJsonStringEqualsJsonString(json_encode($json), $result);
    }

    public function testSerializeWithInvalidHandler(): void
    {
        try {
            Gson::builder()
                ->registerType('foo', new ChildClass())
                ->build();
        } catch (InvalidArgumentException $exception) {
            self::assertSame('Handler of type "Tebru\Gson\Test\Mock\ChildClass" is not supported', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testSerializeWithVisitor(): void
    {
        $gson = Gson::builder()
            ->addTypeAdapterFactory(new CustomTypeAdapter())
            ->addClassMetadataVisitor(new GsonMockMetadataVisitor())
            ->build();

        $result = $gson->toJson($this->gsonMock());
        $json = json_decode($this->json(), true);
        $json['virtual'] = 2;
        unset($json['exclude'], $json['exclude_from_strategy']);

        self::assertJsonStringEqualsJsonString(json_encode($json), $result);
    }

    public function testSerializeSimpleArray(): void
    {
        $gson = Gson::builder()
            ->addTypeAdapterFactory(new CustomTypeAdapter())
            ->build();
        $result = $gson->toNormalized($this->gsonMock());
        $jsonArray = json_decode($this->json(), true);
        $jsonArray['virtual'] = 2;
        unset($jsonArray['exclude']);

        self::assertSame($jsonArray, $result);
    }

    public function testSerializeSimpleArrayOverride(): void
    {
        $gson = Gson::builder()
            ->addTypeAdapterFactory(new CustomTypeAdapter())
            ->build();
        $result = $gson->toNormalized($this->gsonMock(GsonMockResponse::class), GsonMock::class);
        $jsonArray = json_decode($this->json(), true);
        $jsonArray['virtual'] = 2;
        unset($jsonArray['exclude']);

        self::assertSame($jsonArray, $result);
    }

    public function testSerializeInteger(): void
    {
        $gson = Gson::builder()
            ->addTypeAdapterFactory(new CustomTypeAdapter())
            ->build();
        $result = $gson->toNormalized(1);

        self::assertSame(1, $result);
    }

    public function testSerializeBoolean(): void
    {
        $gson = Gson::builder()
            ->addTypeAdapterFactory(new CustomTypeAdapter())
            ->build();
        $result = $gson->toNormalized(false);

        self::assertFalse($result);
    }

    public function testSerializeString(): void
    {
        $gson = Gson::builder()
            ->addTypeAdapterFactory(new CustomTypeAdapter())
            ->build();
        $result = $gson->toNormalized('foo');

        self::assertSame('foo', $result);
    }

    public function testSerializeIntegerWithoutScalarTypeAdapters(): void
    {
        $gson = Gson::builder()
            ->addTypeAdapterFactory(new CustomTypeAdapter())
            ->setEnableScalarAdapters(false)
            ->build();
        $result = $gson->toNormalized(1);

        self::assertSame(1, $result);
    }

    public function testSerializeChildExclude(): void
    {
        $gson = Gson::builder()
            ->addTypeAdapterFactory(new CustomTypeAdapter())
            ->build();

        /** @var GsonMockChild $mock */
        $mock = $this->gsonMock(GsonMockChild::class);
        $mock->id = 1;
        $mock->excluded = true;
        $result = $gson->toJson($mock);
        $expected = ['id' => 1, 'expose' => false];

        self::assertJsonStringEqualsJsonString(json_encode($expected), $result);
    }

    public function testCanSetCacheDirectory(): void
    {
        $gsonBuilder = Gson::builder()->setCacheDir('/tmp');

        self::assertAttributeSame('/tmp/gson', 'cacheDir', $gsonBuilder);
    }

    public function testWillUseFileCache(): void
    {
        $gsonBuilder = Gson::builder()
            ->setCacheDir('/tmp')
            ->enableCache(true);
        $gsonBuilder->build();

        self::assertAttributeInstanceOf(CacheInterface::class, 'cache', $gsonBuilder);
    }

    public function testEnableCacheWithoutDirectoryThrowsException(): void
    {
        try {
            Gson::builder()
                ->enableCache(true)
                ->build();
        } catch (LogicException $exception) {
            self::assertSame('Cannot enable cache without a cache directory', $exception->getMessage());
            return;
        }
        self::assertTrue(false);
    }

    public function testCanOverrideCache(): void
    {
        $cache = CacheProvider::createNullCache();
        $gson = Gson::builder()
            ->setCache($cache)
            ->build();

        $gsonMock = $gson->fromJson($this->json(), GsonMock::class);

        self::assertSame(1, $gsonMock->getInteger());
        self::assertSame(3.2, $gsonMock->getFloat());
        self::assertSame('foo', $gsonMock->getString());
        self::assertFalse($gsonMock->getBoolean());
        self::assertSame(['foo' => 'bar'], $gsonMock->getArray());
        self::assertSame('2017-01-01T12:01:23-06:00', $gsonMock->getDate()->format(DateTime::ATOM));
        self::assertSame('public', $gsonMock->public);
        self::assertAttributeSame('protected', 'protected', $gsonMock);
        self::assertSame('since', $gsonMock->getSince());
        self::assertSame('until', $gsonMock->getUntil());
        self::assertSame('accessor', $gsonMock->getMyAccessor());
        self::assertSame('serializedname', $gsonMock->getSerializedname());
        self::assertSame([1, 2, 3], $gsonMock->getType());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getJsonAdapter());
        self::assertFalse($gsonMock->getExpose());
        self::assertNull($gsonMock->getExclude());
        self::assertTrue($gsonMock->getExcludeFromStrategy());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getGsonObjectMock());
    }

    public function testIncompatibleContext(): void
    {
        $context = new ReaderContext();
        $context->setEnableScalarAdapters(false);

        try {
            Gson::builder()
                ->setWriterContext(new WriterContext())
                ->setReaderContext($context)
                ->build();
        } catch (LogicException $exception) {

            self::assertSame('The "enableScalarAdapter" values for the reader and writer contexts must match', $exception->getMessage());
            return;
        }

        self::fail('Exception was not thrown');
    }

    private function json(): string
    {
        $array = [
            'integer' => 1,
            'float' => 3.2,
            'string' => 'foo',
            'boolean' => false,
            'array' => ['foo' => 'bar'],
            'date' => '2017-01-01T12:01:23-06:00',
            'public' => 'public',
            'protected' => 'protected',
            'since' => 'since',
            'until' => 'until',
            'accessor' => 'accessor',
            'serialized_name' => 'serializedname',
            'type' => [1, 2, 3],
            'json_adapter' => 'bar',
            'expose' => false,
            'exclude' => true,
            'exclude_from_strategy' => true,
            'gson_object_mock' => ['foo' => 'bar'],
        ];

        return json_encode($array);
    }

    private function gsonMock(?string $subclass = null): GsonMock
    {
        $gsonMock = $subclass ? new $subclass() : new GsonMock();
        $gsonMock->setInteger(1);
        $gsonMock->setFloat(3.2);
        $gsonMock->setString('foo');
        $gsonMock->setBoolean(false);
        $gsonMock->setArray(['foo' => 'bar']);
        $gsonMock->setDate(DateTime::createFromFormat(DateTime::ATOM, '2017-01-01T12:01:23-06:00'));
        $gsonMock->public = 'public';
        $gsonMock->setProtectedHidden('protected');
        $gsonMock->setSince('since');
        $gsonMock->setUntil('until');
        $gsonMock->setMyAccessor('accessor');
        $gsonMock->setSerializedname('serializedname');
        $gsonMock->setType([1, 2, 3]);
        $gsonMock->setJsonAdapter(new GsonObjectMock('bar'));
        $gsonMock->setExpose(false);
        $gsonMock->setExclude(true);
        $gsonMock->setExcludeFromStrategy(true);
        $gsonMock->setGsonObjectMock(new GsonObjectMock('bar'));

        return $gsonMock;
    }
}
