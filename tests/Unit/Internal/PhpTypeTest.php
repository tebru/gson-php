<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal;

use ArrayAccess;
use Countable;
use DateTime;
use IteratorAggregate;
use PHPUnit_Framework_TestCase;
use stdClass;
use Tebru\Gson\Exception\MalformedTypeException;
use Tebru\Gson\Internal\DefaultPhpType;
use Tebru\Gson\Test\Mock\Unit\Internal\Data\PhpType\PhpTypeClassParent;
use Tebru\Gson\Test\Mock\Unit\Internal\Data\PhpType\PhpTypeClassWithInterface;
use Tebru\Gson\Test\Mock\Unit\Internal\Data\PhpType\PhpTypeInterface;
use Traversable;

/**
 * Class PhpTypeTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\DefaultPhpType
 * @covers \Tebru\Gson\Internal\TypeToken
 */
class PhpTypeTest extends PHPUnit_Framework_TestCase
{
    public function testConstructWithSpaces()
    {
        $phpType = new DefaultPhpType(' string ');

        self::assertSame('string', (string) $phpType);
    }
    public function testString()
    {
        $phpType = new DefaultPhpType('string');

        self::assertTrue($phpType->isString());
    }

    public function testInteger()
    {
        $phpType = new DefaultPhpType('integer');

        self::assertTrue($phpType->isInteger());
    }

    public function testInt()
    {
        $phpType = new DefaultPhpType('int');

        self::assertTrue($phpType->isInteger());
    }

    public function testFloat()
    {
        $phpType = new DefaultPhpType('float');

        self::assertTrue($phpType->isFloat());
    }

    public function testDouble()
    {
        $phpType = new DefaultPhpType('double');

        self::assertTrue($phpType->isFloat());
    }

    public function testArray()
    {
        $phpType = new DefaultPhpType('array');

        self::assertTrue($phpType->isArray());
    }

    public function testBoolean()
    {
        $phpType = new DefaultPhpType('boolean');

        self::assertTrue($phpType->isBoolean());
    }

    public function testBool()
    {
        $phpType = new DefaultPhpType('bool');

        self::assertTrue($phpType->isBoolean());
    }

    public function testNull()
    {
        $phpType = new DefaultPhpType('null');

        self::assertTrue($phpType->isNull());
    }

    public function testNullCaps()
    {
        $phpType = new DefaultPhpType('NULL');

        self::assertTrue($phpType->isNull());
    }

    public function testResource()
    {
        $phpType = new DefaultPhpType('resource');

        self::assertTrue($phpType->isResource());
    }

    public function testWildcard()
    {
        $phpType = new DefaultPhpType('?');

        self::assertTrue($phpType->isWildcard());
    }

    public function testObject()
    {
        $phpType = new DefaultPhpType('object');

        self::assertTrue($phpType->isObject());
        self::assertSame('stdClass', $phpType->getType());
    }

    public function testStdClass()
    {
        $phpType = new DefaultPhpType('stdClass');

        self::assertTrue($phpType->isObject());
        self::assertSame('stdClass', $phpType->getType());
    }

    public function testCustomClass()
    {
        $phpType = new DefaultPhpType(stdClass::class);

        self::assertTrue($phpType->isObject());
        self::assertSame(stdClass::class, $phpType->getType());
    }

    public function testOneGeneric()
    {
        $phpType = new DefaultPhpType('array<int>');

        self::assertTrue($phpType->isArray());
        self::assertCount(1, $phpType->getGenerics());
        self::assertSame('integer', (string) $phpType->getGenerics()[0]);
    }

    public function testTwoGeneric()
    {
        $phpType = new DefaultPhpType('array<string, int>');

        self::assertTrue($phpType->isArray());
        self::assertCount(2, $phpType->getGenerics());
        self::assertSame('string', (string) $phpType->getGenerics()[0]);
        self::assertSame('integer', (string) $phpType->getGenerics()[1]);
    }

    public function testThreeGeneric()
    {
        $phpType = new DefaultPhpType('stdClass<string, int, stdClass>');

        self::assertTrue($phpType->isObject());
        self::assertSame(stdClass::class, $phpType->getType());
        self::assertCount(3, $phpType->getGenerics());
        self::assertSame('string', (string) $phpType->getGenerics()[0]);
        self::assertSame('integer', (string) $phpType->getGenerics()[1]);
        self::assertSame(stdClass::class, (string) $phpType->getGenerics()[2]->getType());
    }

    public function testNestedGeneric()
    {
        $phpType = new DefaultPhpType('array<array<string, stdClass<string, bool>>>');

        self::assertTrue($phpType->isArray());
        self::assertCount(1, $phpType->getGenerics());
        self::assertTrue($phpType->getGenerics()[0]->isArray());
        self::assertCount(2, $phpType->getGenerics()[0]->getGenerics());
        self::assertSame('string', (string) $phpType->getGenerics()[0]->getGenerics()[0]);
        self::assertSame(stdClass::class, (string) $phpType->getGenerics()[0]->getGenerics()[1]->getType());
        self::assertCount(2, $phpType->getGenerics()[0]->getGenerics()[1]->getGenerics());
        self::assertSame('string', (string) $phpType->getGenerics()[0]->getGenerics()[1]->getGenerics()[0]);
        self::assertSame('boolean', (string) $phpType->getGenerics()[0]->getGenerics()[1]->getGenerics()[1]);
    }

    public function testGenericNoEndingBracket()
    {
        try {
            new DefaultPhpType('array<string');
        } catch (MalformedTypeException $exception) {
            self::assertSame('Could not find ending ">" for generic type', $exception->getMessage());
        }
    }

    public function testGetTypeClass()
    {
        $type = new DefaultPhpType(DateTime::class);

        self::assertSame(DateTime::class, $type->getType());
    }

    public function testGetTypeArray()
    {
        $type = new DefaultPhpType('array');

        self::assertSame('array', $type->getType());
    }

    public function testGetTypeArrayWithGenerics()
    {
        $type = new DefaultPhpType('array<int>');

        self::assertSame('array<int>', $type->getType());
    }

    public function testIsAClass()
    {
        $type = new DefaultPhpType(PhpTypeClassWithInterface::class);

        self::assertTrue($type->isA(PhpTypeClassWithInterface::class));
        self::assertTrue($type->isA(PhpTypeClassParent::class));
        self::assertTrue($type->isA(PhpTypeInterface::class));
        self::assertTrue($type->isA(ArrayAccess::class));
        self::assertTrue($type->isA(IteratorAggregate::class));
        self::assertTrue($type->isA(Countable::class));
        self::assertTrue($type->isA(Traversable::class));
    }

    public function testIsAArray()
    {
        $type = new DefaultPhpType('array');

        self::assertTrue($type->isA('array'));
    }

    public function testIsAArrayGenerics()
    {
        $type = new DefaultPhpType('array<int>');

        self::assertFalse($type->isA('array'));
        self::assertTrue($type->isA('array<int>'));
    }

    public function testIsACanonical()
    {
        $type = new DefaultPhpType('int');

        self::assertTrue($type->isA('integer'));
    }

    public function testOptions()
    {
        $phpType = new DefaultPhpType('DateTime', ['format' => DateTime::ATOM]);

        self::assertSame(DateTime::ATOM, $phpType->getOptions()['format']);
    }

    public function testToString()
    {
        $phpType = new DefaultPhpType('array<array<string, stdClass<string, bool>>>');

        self::assertSame('array<array<string,stdClass<string,bool>>>', (string) $phpType);
    }

    public function testToStringReturnsCanonicalType()
    {
        $phpType = new DefaultPhpType('int');

        self::assertSame('integer', (string) $phpType);
    }

    public function testUniqueKey()
    {
        $phpType = new DefaultPhpType('array');

        self::assertSame('array', $phpType->getUniqueKey());
    }

    public function testUniqueKeyWithGenerics()
    {
        $phpType = new DefaultPhpType('array<int>');

        self::assertSame('array<int>', $phpType->getUniqueKey());
    }

    public function testUniqueKeyWithOptions()
    {
        $phpType = new DefaultPhpType('array', ['foo' => 'bar']);

        self::assertSame('arraya:1:{s:3:"foo";s:3:"bar";}', $phpType->getUniqueKey());
    }

    public function testCreateFromVariableObject()
    {
        self::assertSame(stdClass::class, (string) DefaultPhpType::createFromVariable(new stdClass()));
    }

    public function testCreateFromVariableInteger()
    {
        self::assertSame('integer', (string) DefaultPhpType::createFromVariable(1));
    }

    public function testCreateFromVariableFloat()
    {
        self::assertSame('float', (string) DefaultPhpType::createFromVariable(1.1));
    }

    public function testCreateFromVariableString()
    {
        self::assertSame('string', (string) DefaultPhpType::createFromVariable('foo'));
    }

    public function testCreateFromVariableBooleanTrue()
    {
        self::assertSame('boolean', (string) DefaultPhpType::createFromVariable(true));
    }

    public function testCreateFromVariableBooleanFalse()
    {
        self::assertSame('boolean', (string) DefaultPhpType::createFromVariable(false));
    }

    public function testCreateFromVariableArray()
    {
        self::assertSame('array', (string) DefaultPhpType::createFromVariable([]));
    }

    public function testCreateFromVariableNull()
    {
        self::assertSame('null', (string) DefaultPhpType::createFromVariable(null));
    }
}
