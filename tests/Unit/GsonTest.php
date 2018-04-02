<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit;

use DateTime;
use InvalidArgumentException;
use LogicException;
use PHPUnit_Framework_TestCase;
use ReflectionProperty;
use Symfony\Component\Cache\Simple\ChainCache;
use Symfony\Component\Cache\Simple\NullCache;
use Tebru\Gson\Gson;
use Tebru\Gson\Internal\Naming\UpperCaseMethodNamingStrategy;
use Tebru\Gson\PropertyNamingPolicy;
use Tebru\Gson\Test\Mock\ChildClass;
use Tebru\Gson\Test\Mock\ExclusionStrategies\GsonMockExclusionStrategyMock;
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
class GsonTest extends PHPUnit_Framework_TestCase
{
    public function testSimpleDeserialize()
    {
        $gson = Gson::builder()->build();

        /** @var GsonMock $gsonMock */
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

    public function testDeserializeNotSince()
    {
        $gson = Gson::builder()
            ->setVersion(1)
            ->build();

        /** @var GsonMock $gsonMock */
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

    public function testDeserializeNotUntil()
    {
        $gson = Gson::builder()
            ->setVersion(2)
            ->build();

        /** @var GsonMock $gsonMock */
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

    public function testDeserializeNoProtected()
    {
        $gson = Gson::builder()
            ->setExcludedModifier(ReflectionProperty::IS_PROTECTED)
            ->build();

        /** @var GsonMock $gsonMock */
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

    public function testDeserializeRequireExpose()
    {
        $gson = Gson::builder()
            ->requireExposeAnnotation()
            ->build();

        /** @var GsonMock $gsonMock */
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

    public function testDeserializeCustomTypeAdapter()
    {
        $gson = Gson::builder()
            ->registerType('int', new Integer1TypeAdapter())
            ->build();

        /** @var GsonMock $gsonMock */
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

    public function testDeserializeCustomTypeInterface()
    {
        $gson = Gson::builder()
            ->registerType(GsonObjectMockable::class, new GsonObjectMockTypeAdapterMock())
            ->build();

        /** @var GsonMock $gsonMock */
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

    public function testDeserializeCustomTypeAdapterFactory()
    {
        $gson = Gson::builder()
            ->addTypeAdapterFactory(new Integer1TypeAdapterFactory())
            ->build();

        /** @var GsonMock $gsonMock */
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

    public function testDeserializeCustomDeserializer()
    {
        $gson = Gson::builder()
            ->registerType(GsonMock::class, new Integer1Deserializer())
            ->build();

        /** @var GsonMock $gsonMock */
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

    public function testDeserializeCustomDeserializerBoth()
    {
        $gson = Gson::builder()
            ->registerType(GsonMock::class, new Integer1SerializerDeserializer())
            ->build();

        /** @var GsonMock $gsonMock */
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

    public function testDeserializeUsingInstanceCreator()
    {
        $gson = Gson::builder()
            ->addInstanceCreator(GsonObjectMock::class, new GsonObjectMockInstanceCreatorMock())
            ->build();

        /** @var GsonMock $gsonMock */
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

    public function testDeserializeUsingInstanceCreatorInterface()
    {
        $gson = Gson::builder()
            ->addInstanceCreator(GsonObjectMockable::class, new GsonObjectMockInstanceCreatorMock())
            ->build();

        /** @var GsonMock $gsonMock */
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

    public function testDeserializeWithExclusionStrategy()
    {
        $gson = Gson::builder()
            ->addExclusionStrategy(new GsonMockExclusionStrategyMock(), true, true)
            ->build();

        /** @var GsonMock $gsonMock */
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

    public function testDeserializeWithPropertyNamingPolicy()
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

        /** @var GsonMock $gsonMock */
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

    public function testDeserializeWithPropertyNamingStrategy()
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

        /** @var GsonMock $gsonMock */
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

    public function testDeserializeWithMethodNamingStrategy()
    {
        $gson = Gson::builder()
            ->setMethodNamingStrategy(new UpperCaseMethodNamingStrategy())
            ->build();

        /** @var GsonMock $gsonMock */
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



    public function testDeserializeUsesSameObject()
    {
        $gsonMock = new GsonMock();
        $gsonMock->setExclude(false);

        $gson = Gson::builder()->build();

        /** @var GsonMock $gsonMock */
        $returnedObject = $gson->fromJson($this->json(), $gsonMock);

        self::assertSame($gsonMock, $returnedObject);
    }

    public function testDeserializeUsesSameObjectNested()
    {
        $gsonMock = new GsonMock();
        $gsonMock->setExclude(false);
        $gsonMock->setGsonObjectMock(new GsonObjectMock('test'));

        $gson = Gson::builder()->build();

        /** @var GsonMock $gsonMock */
        $returnedObject = $gson->fromJson($this->json(), $gsonMock);

        self::assertSame($gsonMock, $returnedObject);
    }

    public function testSimpleDeserializeArray()
    {
        $gson = Gson::builder()->build();

        /** @var GsonMock $gsonMock */
        $gsonMock = $gson->fromArray(json_decode($this->json(), true), GsonMock::class);

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

    public function testSerializeSimple()
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

    public function testSerializeDateTimeFormat()
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

    public function testSerializeNulls()
    {
        $gson = Gson::builder()
            ->addTypeAdapterFactory(new CustomTypeAdapter())
            ->serializeNull()
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

    public function testSerializeNotSince()
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

    public function testSerializeNotUntil()
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

    public function testSerializeNotProtected()
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

    public function testSerializeRequireExpose()
    {
        $gson = Gson::builder()
            ->requireExposeAnnotation()
            ->build();

        $result = $gson->toJson($this->gsonMock());

        self::assertJsonStringEqualsJsonString('{"expose": false}', $result);
    }

    public function testSerializeCustomTypeAdapter()
    {
        $gson = Gson::builder()
            ->addTypeAdapterFactory(new CustomTypeAdapter())
            ->registerType('int', new Integer1TypeAdapter())
            ->build();

        $result = $gson->toJson($this->gsonMock());
        $json = json_decode($this->json(), true);
        unset($json['exclude']);
        $json['virtual'] = 3;
        $json['integer'] = 2;
        $json['type'] = [2, 3, 4];

        self::assertJsonStringEqualsJsonString(json_encode($json), $result);
    }

    public function testSerializeCustomTypeAdapterFactory()
    {
        $gson = Gson::builder()
            ->addTypeAdapterFactory(new CustomTypeAdapter())
            ->addTypeAdapterFactory(new Integer1TypeAdapterFactory())
            ->build();

        $result = $gson->toJson($this->gsonMock());
        $json = json_decode($this->json(), true);
        $json['virtual'] = 3;
        unset($json['exclude']);
        $json['integer'] = 2;
        $json['type'] = [2, 3, 4];

        self::assertJsonStringEqualsJsonString(json_encode($json), $result);
    }

    public function testSerializeCustomSerializer()
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

    public function testSerializeWithInvalidHandler()
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

    public function testSerializeWithExclusionStrategy()
    {
        $gson = Gson::builder()
            ->addTypeAdapterFactory(new CustomTypeAdapter())
            ->addExclusionStrategy(new GsonMockExclusionStrategyMock(), true, true)
            ->build();

        $result = $gson->toJson($this->gsonMock());
        $json = json_decode($this->json(), true);
        $json['virtual'] = 2;
        unset($json['exclude'], $json['exclude_from_strategy']);

        self::assertJsonStringEqualsJsonString(json_encode($json), $result);
    }

    public function testSerializeSimpleArray()
    {
        $gson = Gson::builder()
            ->addTypeAdapterFactory(new CustomTypeAdapter())
            ->build();
        $result = $gson->toArray($this->gsonMock());
        $jsonArray = json_decode($this->json(), true);
        $jsonArray['virtual'] = 2;
        unset($jsonArray['exclude']);

        self::assertSame($jsonArray, $result);
    }

    public function testSerializeIntegerArray()
    {
        $gson = Gson::builder()
            ->addTypeAdapterFactory(new CustomTypeAdapter())
            ->build();
        $result = $gson->toArray(1);

        self::assertSame([1], $result);
    }

    public function testSerializeBooleanArray()
    {
        $gson = Gson::builder()
            ->addTypeAdapterFactory(new CustomTypeAdapter())
            ->build();
        $result = $gson->toArray(false);

        self::assertSame([false], $result);
    }

    public function testSerializeStringArray()
    {
        $gson = Gson::builder()
            ->addTypeAdapterFactory(new CustomTypeAdapter())
            ->build();
        $result = $gson->toArray('foo');

        self::assertSame(['foo'], $result);
    }

    public function testToJsonElement()
    {
        $gson = Gson::builder()
            ->addTypeAdapterFactory(new CustomTypeAdapter())
            ->build();
        $result = $gson->toJsonElement($this->gsonMock());
        $json = json_decode($this->json(), true);
        $json['virtual'] = 2;
        unset($json['exclude']);

        self::assertJsonStringEqualsJsonString(json_encode($json), json_encode($result));
    }

    public function testDifferentInstancesWillUseDifferentTypeAdapterCaches()
    {
        $exclusionStrategy = new GsonMockExclusionStrategyMock();
        $gson = Gson::builder()
            ->addTypeAdapterFactory(new CustomTypeAdapter())
            ->addExclusionStrategy($exclusionStrategy, true, true)
            ->build();
        $result = $gson->toJsonElement($this->gsonMock());

        // stop excluding
        $exclusionStrategy->skipProperty = false;

        $gson = Gson::builder()
            ->addTypeAdapterFactory(new CustomTypeAdapter())
            ->addExclusionStrategy($exclusionStrategy, true, true)
            ->build();
        $result2 = $gson->toJsonElement($this->gsonMock());

        // excluder is not cached
        self::assertNotEquals($result, $result2);
    }

    public function testCanSetCacheDirectory()
    {
        $gsonBuilder = Gson::builder()->setCacheDir('/tmp');

        self::assertAttributeSame('/tmp/gson', 'cacheDir', $gsonBuilder);
    }

    public function testWillUseFileCache()
    {
        $gsonBuilder = Gson::builder()
            ->setCacheDir('/tmp')
            ->enableCache(true);
        $gsonBuilder->build();

        self::assertAttributeInstanceOf(ChainCache::class, 'cache', $gsonBuilder);
    }

    public function testEnableCacheWithoutDirectoryThrowsException()
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

    public function testCanOverrideCache()
    {
        $gson = Gson::builder()
            ->setCache(new NullCache())
            ->build();

        /** @var GsonMock $gsonMock */
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

    private function gsonMock(): GsonMock
    {
        $gsonMock = new GsonMock();
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
