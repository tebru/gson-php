<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit;

use DateTime;
use PHPUnit_Framework_TestCase;
use stdClass;
use Tebru\Gson\Exception\MalformedTypeException;
use Tebru\Gson\PhpType;

/**
 * Class PhpTypeTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\PhpType
 * @covers \Tebru\Gson\Internal\TypeToken
 */
class PhpTypeTest extends PHPUnit_Framework_TestCase
{
    public function testConstructWithSpaces()
    {
        $phpType = new PhpType(' string ');

        self::assertSame('string', (string) $phpType);
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
        $phpType = new PhpType('array<int>');

        self::assertTrue($phpType->isArray());
        self::assertCount(1, $phpType->getGenerics());
        self::assertSame('integer', (string) $phpType->getGenerics()[0]);
    }

    public function testTwoGeneric()
    {
        $phpType = new PhpType('array<string, int>');

        self::assertTrue($phpType->isArray());
        self::assertCount(2, $phpType->getGenerics());
        self::assertSame('string', (string) $phpType->getGenerics()[0]);
        self::assertSame('integer', (string) $phpType->getGenerics()[1]);
    }

    public function testThreeGeneric()
    {
        $phpType = new PhpType('Foo<string, int, Bar>');

        self::assertTrue($phpType->isObject());
        self::assertSame('Foo', $phpType->getClass());
        self::assertCount(3, $phpType->getGenerics());
        self::assertSame('string', (string) $phpType->getGenerics()[0]);
        self::assertSame('integer', (string) $phpType->getGenerics()[1]);
        self::assertSame('Bar', (string) $phpType->getGenerics()[2]->getClass());
    }

    public function testNestedGeneric()
    {
        $phpType = new PhpType('array<array<string, Bar<string, bool>>>');

        self::assertTrue($phpType->isArray());
        self::assertCount(1, $phpType->getGenerics());
        self::assertTrue($phpType->getGenerics()[0]->isArray());
        self::assertCount(2, $phpType->getGenerics()[0]->getGenerics());
        self::assertSame('string', (string) $phpType->getGenerics()[0]->getGenerics()[0]);
        self::assertSame('Bar', (string) $phpType->getGenerics()[0]->getGenerics()[1]->getClass());
        self::assertCount(2, $phpType->getGenerics()[0]->getGenerics()[1]->getGenerics());
        self::assertSame('string', (string) $phpType->getGenerics()[0]->getGenerics()[1]->getGenerics()[0]);
        self::assertSame('boolean', (string) $phpType->getGenerics()[0]->getGenerics()[1]->getGenerics()[1]);
    }

    public function testGenericNoEndingBracket()
    {
        $this->expectException(MalformedTypeException::class);
        $this->expectExceptionMessage('Could not find ending ">" for generic type');

        new PhpType('array<string');
    }

    public function testOptions()
    {
        $phpType = new PhpType('DateTime');
        $phpType->setOptions(['format' => DateTime::ATOM]);

        self::assertSame(DateTime::ATOM, $phpType->getOptions()['format']);
    }

    public function testToString()
    {
        $phpType = new PhpType('array<array<string, Bar<string, bool>>>');

        self::assertSame('array<array<string,Bar<string,bool>>>', (string) $phpType);
    }

    public function testToStringReturnsCanonicalType()
    {
        $phpType = new PhpType('int');

        self::assertSame('integer', (string) $phpType);
    }

    public function testCreateFromVariableObject()
    {
        self::assertSame(stdClass::class, (string) PhpType::createFromVariable(new stdClass()));
    }

    public function testCreateFromVariableInteger()
    {
        self::assertSame('integer', (string) PhpType::createFromVariable(1));
    }

    public function testCreateFromVariableFloat()
    {
        self::assertSame('float', (string) PhpType::createFromVariable(1.1));
    }

    public function testCreateFromVariableString()
    {
        self::assertSame('string', (string) PhpType::createFromVariable('foo'));
    }

    public function testCreateFromVariableBooleanTrue()
    {
        self::assertSame('boolean', (string) PhpType::createFromVariable(true));
    }

    public function testCreateFromVariableBooleanFalse()
    {
        self::assertSame('boolean', (string) PhpType::createFromVariable(false));
    }

    public function testCreateFromVariableArray()
    {
        self::assertSame('array', (string) PhpType::createFromVariable([]));
    }

    public function testCreateFromVariableNull()
    {
        self::assertSame('null', (string) PhpType::createFromVariable(null));
    }
}
