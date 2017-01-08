<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal;

use DateTime;
use PHPUnit_Framework_TestCase;
use Tebru\Gson\Exception\MalformedTypeException;
use Tebru\Gson\Internal\PhpType;

/**
 * Class PhpTypeTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\PhpType
 * @covers \Tebru\Gson\Internal\TypeToken
 */
class PhpTypeTest extends PHPUnit_Framework_TestCase
{
    public function testConstructWithSpaces()
    {
        $phpType = new PhpType(' string ');

        self::assertSame('string', (string) $phpType->getType());
    }
    public function testString()
    {
        $phpType = new PhpType('string');

        self::assertTrue($phpType->isString());
    }

    public function testInteger()
    {
        $phpType = new PhpType('integer');

        self::assertTrue($phpType->isInteger());
    }

    public function testInt()
    {
        $phpType = new PhpType('int');

        self::assertTrue($phpType->isInteger());
    }

    public function testFloat()
    {
        $phpType = new PhpType('float');

        self::assertTrue($phpType->isFloat());
    }

    public function testDouble()
    {
        $phpType = new PhpType('double');

        self::assertTrue($phpType->isFloat());
    }

    public function testArray()
    {
        $phpType = new PhpType('array');

        self::assertTrue($phpType->isArray());
    }

    public function testBoolean()
    {
        $phpType = new PhpType('boolean');

        self::assertTrue($phpType->isBoolean());
    }

    public function testBool()
    {
        $phpType = new PhpType('bool');

        self::assertTrue($phpType->isBoolean());
    }

    public function testNull()
    {
        $phpType = new PhpType('null');

        self::assertTrue($phpType->isNull());
    }

    public function testNullCaps()
    {
        $phpType = new PhpType('NULL');

        self::assertTrue($phpType->isNull());
    }

    public function testResource()
    {
        $phpType = new PhpType('resource');

        self::assertTrue($phpType->isResource());
    }

    public function testWildcard()
    {
        $phpType = new PhpType('?');

        self::assertTrue($phpType->isWildcard());
    }

    public function testObject()
    {
        $phpType = new PhpType('object');

        self::assertTrue($phpType->isObject());
        self::assertSame('stdClass', $phpType->getClass());
    }

    public function testStdClass()
    {
        $phpType = new PhpType('stdClass');

        self::assertTrue($phpType->isObject());
        self::assertSame('stdClass', $phpType->getClass());
    }

    public function testCustomClass()
    {
        $phpType = new PhpType('Foo');

        self::assertTrue($phpType->isObject());
        self::assertSame('Foo', $phpType->getClass());
    }

    public function testOneGeneric()
    {
        $phpType = new PhpType('ArrayList<int>');

        self::assertTrue($phpType->isObject());
        self::assertSame('ArrayList', $phpType->getClass());
        self::assertCount(1, $phpType->getGenerics());
        self::assertSame('integer', (string) $phpType->getGenerics()->get(0)->getType());
    }

    public function testTwoGeneric()
    {
        $phpType = new PhpType('HashMap<string, int>');

        self::assertTrue($phpType->isObject());
        self::assertSame('HashMap', $phpType->getClass());
        self::assertCount(2, $phpType->getGenerics());
        self::assertSame('string', (string) $phpType->getGenerics()->get(0)->getType());
        self::assertSame('integer', (string) $phpType->getGenerics()->get(1)->getType());
    }

    public function testThreeGeneric()
    {
        $phpType = new PhpType('Foo<string, int, Bar>');

        self::assertTrue($phpType->isObject());
        self::assertSame('Foo', $phpType->getClass());
        self::assertCount(3, $phpType->getGenerics());
        self::assertSame('string', (string) $phpType->getGenerics()->get(0)->getType());
        self::assertSame('integer', (string) $phpType->getGenerics()->get(1)->getType());
        self::assertSame('object', (string) $phpType->getGenerics()->get(2)->getType());
        self::assertSame('Bar', (string) $phpType->getGenerics()->get(2)->getClass());
    }

    public function testNestedGeneric()
    {
        $phpType = new PhpType('ArrayList<HashMap<string, Bar<string, bool>>>');

        self::assertTrue($phpType->isObject());
        self::assertSame('ArrayList', $phpType->getClass());
        self::assertCount(1, $phpType->getGenerics());
        self::assertSame('object', (string) $phpType->getGenerics()->get(0)->getType());
        self::assertSame('HashMap', (string) $phpType->getGenerics()->get(0)->getClass());
        self::assertCount(2, $phpType->getGenerics()->get(0)->getGenerics());
        self::assertSame('string', (string) $phpType->getGenerics()->get(0)->getGenerics()->get(0)->getType());
        self::assertSame('object', (string) $phpType->getGenerics()->get(0)->getGenerics()->get(1)->getType());
        self::assertSame('Bar', (string) $phpType->getGenerics()->get(0)->getGenerics()->get(1)->getClass());
        self::assertCount(2, $phpType->getGenerics()->get(0)->getGenerics()->get(1)->getGenerics());
        self::assertSame('string', (string) $phpType->getGenerics()->get(0)->getGenerics()->get(1)->getGenerics()->get(0)->getType());
        self::assertSame('boolean', (string) $phpType->getGenerics()->get(0)->getGenerics()->get(1)->getGenerics()->get(1)->getType());
    }

    public function testGenericNoEndingBracket()
    {
        $this->expectException(MalformedTypeException::class);
        $this->expectExceptionMessage('Could not find ending ">" for generic type');

        new PhpType('ArrayList<string');
    }

    public function testOptions()
    {
        $phpType = new PhpType('DateTime');
        $phpType->setOptions(['format' => DateTime::ATOM]);

        self::assertSame(DateTime::ATOM, $phpType->getOptions()->get('format'));
    }

    public function testToString()
    {
        $phpType = new PhpType('ArrayList<HashMap<string, Bar<string, bool>>>');

        self::assertSame('ArrayList<HashMap<string,Bar<string,bool>>>', (string) $phpType);
    }

    public function testToStringReturnsCanonicalType()
    {
        $phpType = new PhpType('int');

        self::assertSame('integer', (string) $phpType);
    }
}
