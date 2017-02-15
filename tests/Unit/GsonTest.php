<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
namespace Tebru\Gson\Test\Unit;

use DateTime;
use PHPUnit_Framework_TestCase;
use ReflectionProperty;
use Tebru\Gson\Gson;
use Tebru\Gson\Internal\Naming\UpperCaseMethodNamingStrategy;
use Tebru\Gson\Test\Mock\ExclusionStrategies\GsonMockExclusionStrategyMock;
use Tebru\Gson\Test\Mock\GsonObjectMock;
use Tebru\Gson\Test\Mock\GsonMock;
use Tebru\Gson\Test\Mock\Strategy\TwoPropertyNamingStrategy;
use Tebru\Gson\Test\Mock\TypeAdapter\Integer1Deserializer;
use Tebru\Gson\Test\Mock\TypeAdapter\Integer1TypeAdapter;
use Tebru\Gson\Test\Mock\TypeAdapter\Integer1TypeAdapterFactory;

/**
 * Class GsonTest
 *
 * @author Nate Brunette <n@tebru.net>
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
        self::assertSame(false, $gsonMock->getBoolean());
        self::assertSame(['foo' => 'bar'], $gsonMock->getArray());
        self::assertSame([1.1, 1.2], $gsonMock->getArrayList()->toArray());
        self::assertSame('value', $gsonMock->getHashMap()->get('key'));
        self::assertSame('2017-01-01T12:01:23-06:00', $gsonMock->getDate()->format(DateTime::ATOM));
        self::assertSame('public', $gsonMock->public);
        self::assertAttributeSame('protected', 'protected', $gsonMock);
        self::assertSame('since', $gsonMock->getSince());
        self::assertSame('until', $gsonMock->getUntil());
        self::assertSame('accessor', $gsonMock->getMyAccessor());
        self::assertSame('serializedname', $gsonMock->getSerializedname());
        self::assertSame([1, 2, 3], $gsonMock->getType());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getJsonAdapter());
        self::assertSame(false, $gsonMock->getExpose());
        self::assertSame(null, $gsonMock->getExclude());
        self::assertSame(true, $gsonMock->getExcludeFromStrategy());
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
        self::assertSame(false, $gsonMock->getBoolean());
        self::assertSame(['foo' => 'bar'], $gsonMock->getArray());
        self::assertSame([1.1, 1.2], $gsonMock->getArrayList()->toArray());
        self::assertSame('value', $gsonMock->getHashMap()->get('key'));
        self::assertSame('2017-01-01T12:01:23-06:00', $gsonMock->getDate()->format(DateTime::ATOM));
        self::assertSame('public', $gsonMock->public);
        self::assertAttributeSame('protected', 'protected', $gsonMock);
        self::assertSame(null, $gsonMock->getSince());
        self::assertSame('until', $gsonMock->getUntil());
        self::assertSame('accessor', $gsonMock->getMyAccessor());
        self::assertSame('serializedname', $gsonMock->getSerializedname());
        self::assertSame([1, 2, 3], $gsonMock->getType());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getJsonAdapter());
        self::assertSame(false, $gsonMock->getExpose());
        self::assertSame(null, $gsonMock->getExclude());
        self::assertSame(true, $gsonMock->getExcludeFromStrategy());
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
        self::assertSame(false, $gsonMock->getBoolean());
        self::assertSame(['foo' => 'bar'], $gsonMock->getArray());
        self::assertSame([1.1, 1.2], $gsonMock->getArrayList()->toArray());
        self::assertSame('value', $gsonMock->getHashMap()->get('key'));
        self::assertSame('2017-01-01T12:01:23-06:00', $gsonMock->getDate()->format(DateTime::ATOM));
        self::assertSame('public', $gsonMock->public);
        self::assertAttributeSame('protected', 'protected', $gsonMock);
        self::assertSame('since', $gsonMock->getSince());
        self::assertSame(null, $gsonMock->getUntil());
        self::assertSame('accessor', $gsonMock->getMyAccessor());
        self::assertSame('serializedname', $gsonMock->getSerializedname());
        self::assertSame([1, 2, 3], $gsonMock->getType());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getJsonAdapter());
        self::assertSame(false, $gsonMock->getExpose());
        self::assertSame(null, $gsonMock->getExclude());
        self::assertSame(true, $gsonMock->getExcludeFromStrategy());
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
        self::assertSame(false, $gsonMock->getBoolean());
        self::assertSame(['foo' => 'bar'], $gsonMock->getArray());
        self::assertSame([1.1, 1.2], $gsonMock->getArrayList()->toArray());
        self::assertSame('value', $gsonMock->getHashMap()->get('key'));
        self::assertSame('2017-01-01T12:01:23-06:00', $gsonMock->getDate()->format(DateTime::ATOM));
        self::assertSame('public', $gsonMock->public);
        self::assertAttributeSame(null, 'protected', $gsonMock);
        self::assertSame('since', $gsonMock->getSince());
        self::assertSame('until', $gsonMock->getUntil());
        self::assertSame('accessor', $gsonMock->getMyAccessor());
        self::assertSame('serializedname', $gsonMock->getSerializedname());
        self::assertSame([1, 2, 3], $gsonMock->getType());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getJsonAdapter());
        self::assertSame(false, $gsonMock->getExpose());
        self::assertSame(null, $gsonMock->getExclude());
        self::assertSame(true, $gsonMock->getExcludeFromStrategy());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getGsonObjectMock());
    }

    public function testDeserializeRequireExpose()
    {
        $gson = Gson::builder()
            ->requireExposeAnnotation()
            ->build();

        /** @var GsonMock $gsonMock */
        $gsonMock = $gson->fromJson($this->json(), GsonMock::class);

        self::assertSame(1, $gsonMock->getInteger());
        self::assertSame(3.2, $gsonMock->getFloat());
        self::assertSame('foo', $gsonMock->getString());
        self::assertSame(false, $gsonMock->getBoolean());
        self::assertSame(['foo' => 'bar'], $gsonMock->getArray());
        self::assertSame([1.1, 1.2], $gsonMock->getArrayList()->toArray());
        self::assertSame('value', $gsonMock->getHashMap()->get('key'));
        self::assertSame(null, $gsonMock->getDate());
        self::assertSame('public', $gsonMock->public);
        self::assertAttributeSame('protected', 'protected', $gsonMock);
        self::assertSame('since', $gsonMock->getSince());
        self::assertSame('until', $gsonMock->getUntil());
        self::assertSame('accessor', $gsonMock->getMyAccessor());
        self::assertSame('serializedname', $gsonMock->getSerializedname());
        self::assertSame([1, 2, 3], $gsonMock->getType());
        self::assertSame(null, $gsonMock->getJsonAdapter());
        self::assertSame(false, $gsonMock->getExpose());
        self::assertSame(null, $gsonMock->getExclude());
        self::assertSame(true, $gsonMock->getExcludeFromStrategy());
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
        self::assertSame(false, $gsonMock->getBoolean());
        self::assertSame(['foo' => 'bar'], $gsonMock->getArray());
        self::assertSame([1.1, 1.2], $gsonMock->getArrayList()->toArray());
        self::assertSame('value', $gsonMock->getHashMap()->get('key'));
        self::assertSame('2017-01-01T12:01:23-06:00', $gsonMock->getDate()->format(DateTime::ATOM));
        self::assertSame('public', $gsonMock->public);
        self::assertAttributeSame('protected', 'protected', $gsonMock);
        self::assertSame('since', $gsonMock->getSince());
        self::assertSame('until', $gsonMock->getUntil());
        self::assertSame('accessor', $gsonMock->getMyAccessor());
        self::assertSame('serializedname', $gsonMock->getSerializedname());
        self::assertSame([2, 3, 4], $gsonMock->getType());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getJsonAdapter());
        self::assertSame(false, $gsonMock->getExpose());
        self::assertSame(null, $gsonMock->getExclude());
        self::assertSame(true, $gsonMock->getExcludeFromStrategy());
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
        self::assertSame(false, $gsonMock->getBoolean());
        self::assertSame(['foo' => 'bar'], $gsonMock->getArray());
        self::assertSame([1.1, 1.2], $gsonMock->getArrayList()->toArray());
        self::assertSame('value', $gsonMock->getHashMap()->get('key'));
        self::assertSame('2017-01-01T12:01:23-06:00', $gsonMock->getDate()->format(DateTime::ATOM));
        self::assertSame('public', $gsonMock->public);
        self::assertAttributeSame('protected', 'protected', $gsonMock);
        self::assertSame('since', $gsonMock->getSince());
        self::assertSame('until', $gsonMock->getUntil());
        self::assertSame('accessor', $gsonMock->getMyAccessor());
        self::assertSame('serializedname', $gsonMock->getSerializedname());
        self::assertSame([2, 3, 4], $gsonMock->getType());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getJsonAdapter());
        self::assertSame(false, $gsonMock->getExpose());
        self::assertSame(null, $gsonMock->getExclude());
        self::assertSame(true, $gsonMock->getExcludeFromStrategy());
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
        self::assertSame(false, $gsonMock->getBoolean());
        self::assertSame(['foo' => 'bar'], $gsonMock->getArray());
        self::assertSame([1.1, 1.2], $gsonMock->getArrayList()->toArray());
        self::assertSame('value', $gsonMock->getHashMap()->get('key'));
        self::assertSame('2017-01-01T12:01:23-06:00', $gsonMock->getDate()->format(DateTime::ATOM));
        self::assertSame('public', $gsonMock->public);
        self::assertAttributeSame(null, 'protected', $gsonMock);
        self::assertSame('since', $gsonMock->getSince());
        self::assertSame('until', $gsonMock->getUntil());
        self::assertSame('accessor', $gsonMock->getMyAccessor());
        self::assertSame('serializedname', $gsonMock->getSerializedname());
        self::assertSame([2, 3, 4], $gsonMock->getType());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getJsonAdapter());
        self::assertSame(false, $gsonMock->getExpose());
        self::assertSame(null, $gsonMock->getExclude());
        self::assertSame(true, $gsonMock->getExcludeFromStrategy());
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
        self::assertSame(false, $gsonMock->getBoolean());
        self::assertSame(['foo' => 'bar'], $gsonMock->getArray());
        self::assertSame([1.1, 1.2], $gsonMock->getArrayList()->toArray());
        self::assertSame('value', $gsonMock->getHashMap()->get('key'));
        self::assertSame('2017-01-01T12:01:23-06:00', $gsonMock->getDate()->format(DateTime::ATOM));
        self::assertSame('public', $gsonMock->public);
        self::assertAttributeSame('protected', 'protected', $gsonMock);
        self::assertSame('since', $gsonMock->getSince());
        self::assertSame('until', $gsonMock->getUntil());
        self::assertSame('accessor', $gsonMock->getMyAccessor());
        self::assertSame('serializedname', $gsonMock->getSerializedname());
        self::assertSame([1, 2, 3], $gsonMock->getType());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getJsonAdapter());
        self::assertSame(false, $gsonMock->getExpose());
        self::assertSame(null, $gsonMock->getExclude());
        self::assertSame(null, $gsonMock->getExcludeFromStrategy());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getGsonObjectMock());
    }

    public function testDeserializeWithPropertyNamingStrategy()
    {
        $gson = Gson::builder()
            ->setPropertyNamingStrategy(new TwoPropertyNamingStrategy())
            ->build();

        /** @var GsonMock $gsonMock */
        $gsonMock = $gson->fromJson($this->json2(), GsonMock::class);

        self::assertSame(1, $gsonMock->getInteger());
        self::assertSame(3.2, $gsonMock->getFloat());
        self::assertSame('foo', $gsonMock->getString());
        self::assertSame(false, $gsonMock->getBoolean());
        self::assertSame(['foo' => 'bar'], $gsonMock->getArray());
        self::assertSame([1.1, 1.2], $gsonMock->getArrayList()->toArray());
        self::assertSame('value', $gsonMock->getHashMap()->get('key'));
        self::assertSame('2017-01-01T12:01:23-06:00', $gsonMock->getDate()->format(DateTime::ATOM));
        self::assertSame('public', $gsonMock->public);
        self::assertAttributeSame('protected', 'protected', $gsonMock);
        self::assertSame('since', $gsonMock->getSince());
        self::assertSame('until', $gsonMock->getUntil());
        self::assertSame('accessor', $gsonMock->getMyAccessor());
        self::assertSame('serializedname', $gsonMock->getSerializedname());
        self::assertSame([1, 2, 3], $gsonMock->getType());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getJsonAdapter());
        self::assertSame(false, $gsonMock->getExpose());
        self::assertSame(null, $gsonMock->getExclude());
        self::assertSame(true, $gsonMock->getExcludeFromStrategy());
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
        self::assertSame(false, $gsonMock->getBoolean());
        self::assertSame(['foo' => 'bar'], $gsonMock->getArray());
        self::assertSame([1.1, 1.2], $gsonMock->getArrayList()->toArray());
        self::assertSame('value', $gsonMock->getHashMap()->get('key'));
        self::assertSame('2017-01-01T12:01:23-06:00', $gsonMock->getDate()->format(DateTime::ATOM));
        self::assertSame('public', $gsonMock->public);
        self::assertAttributeSame('protected', 'protected', $gsonMock);
        self::assertSame('since', $gsonMock->getSince());
        self::assertSame('until', $gsonMock->getUntil());
        self::assertSame('accessor', $gsonMock->getMyAccessor());
        self::assertSame('serializedname', $gsonMock->getSerializedname());
        self::assertSame([1, 2, 3], $gsonMock->getType());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getJsonAdapter());
        self::assertSame(false, $gsonMock->getExpose());
        self::assertSame(null, $gsonMock->getExclude());
        self::assertSame(true, $gsonMock->getExcludeFromStrategy());
        self::assertEquals(new GsonObjectMock('bar'), $gsonMock->getGsonObjectMock());
    }

    public function testDeserializeUsesSameObject()
    {
        $gsonMock = new GsonMock();
        $gsonMock->setExclude(false);

        $gson = Gson::builder()->build();

        /** @var GsonMock $gsonMock */
        $gsonMock = $gson->fromJson($this->json(), $gsonMock);

        self::assertSame(false, $gsonMock->getExclude());
    }

    private function json()
    {
        $array = [
            'integer' => 1,
            'float' => 3.2,
            'string' => 'foo',
            'boolean' => false,
            'array' => ['foo' => 'bar'],
            'array_list' => [1.1, 1.2],
            'hash_map' => ['key' => 'value'],
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

    private function json2()
    {
        $array = [
            'integer2' => 1,
            'float2' => 3.2,
            'string2' => 'foo',
            'boolean2' => false,
            'array2' => ['foo' => 'bar'],
            'arrayList2' => [1.1, 1.2],
            'hashMap2' => ['key' => 'value'],
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

        return json_encode($array);
    }
}
