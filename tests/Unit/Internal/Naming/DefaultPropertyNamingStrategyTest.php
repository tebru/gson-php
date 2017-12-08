<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\Naming;

use PHPUnit_Framework_TestCase;
use Tebru\Gson\Internal\Naming\DefaultPropertyNamingStrategy;
use Tebru\Gson\PropertyNamingPolicy;

/**
 * Class DefaultPropertyNamingStrategyTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\Naming\DefaultPropertyNamingStrategy
 */
class DefaultPropertyNamingStrategyTest extends PHPUnit_Framework_TestCase
{
    public function testIdentity()
    {
        $propertyNaming = new DefaultPropertyNamingStrategy(PropertyNamingPolicy::IDENTITY);

        self::assertSame('foo', $propertyNaming->translateName('foo'));
    }

    public function testIdentityComplex()
    {
        $propertyNaming = new DefaultPropertyNamingStrategy(PropertyNamingPolicy::IDENTITY);

        self::assertSame('FooBarBazAF1Z', $propertyNaming->translateName('FooBarBazAF1Z'));
    }

    public function testLowerCaseDashesNoTransform()
    {
        $propertyNaming = new DefaultPropertyNamingStrategy(PropertyNamingPolicy::LOWER_CASE_WITH_DASHES);

        self::assertSame('foo', $propertyNaming->translateName('foo'));
    }

    public function testLowerCaseDashesSimpleTransform()
    {
        $propertyNaming = new DefaultPropertyNamingStrategy(PropertyNamingPolicy::LOWER_CASE_WITH_DASHES);

        self::assertSame('foo-bar', $propertyNaming->translateName('fooBar'));
    }

    public function testLowerCaseDashesComplexTransform()
    {
        $propertyNaming = new DefaultPropertyNamingStrategy(PropertyNamingPolicy::LOWER_CASE_WITH_DASHES);

        self::assertSame('foo-bar-baz-a-f1-z', $propertyNaming->translateName('FooBarBazAF1Z'));
    }

    public function testLowerCaseUnderscoreNoTransform()
    {
        $propertyNaming = new DefaultPropertyNamingStrategy(PropertyNamingPolicy::LOWER_CASE_WITH_UNDERSCORES);

        self::assertSame('foo', $propertyNaming->translateName('foo'));
    }

    public function testLowerCaseUnderscoreSimpleTransform()
    {
        $propertyNaming = new DefaultPropertyNamingStrategy(PropertyNamingPolicy::LOWER_CASE_WITH_UNDERSCORES);

        self::assertSame('foo_bar', $propertyNaming->translateName('fooBar'));
    }

    public function testLowerCaseUnderscoreComplex()
    {
        $propertyNaming = new DefaultPropertyNamingStrategy(PropertyNamingPolicy::LOWER_CASE_WITH_UNDERSCORES);

        self::assertSame('foo_bar_baz_a_f1_z', $propertyNaming->translateName('FooBarBazAF1Z'));
    }
    #

    public function testUpperCaseDashesNoTransform()
    {
        $propertyNaming = new DefaultPropertyNamingStrategy(PropertyNamingPolicy::UPPER_CASE_WITH_DASHES);

        self::assertSame('FOO', $propertyNaming->translateName('foo'));
    }

    public function testUpperCaseDashesSimpleTransform()
    {
        $propertyNaming = new DefaultPropertyNamingStrategy(PropertyNamingPolicy::UPPER_CASE_WITH_DASHES);

        self::assertSame('FOO-BAR', $propertyNaming->translateName('fooBar'));
    }

    public function testUpperCaseDashesComplexTransform()
    {
        $propertyNaming = new DefaultPropertyNamingStrategy(PropertyNamingPolicy::UPPER_CASE_WITH_DASHES);

        self::assertSame('FOO-BAR-BAZ-A-F1-Z', $propertyNaming->translateName('FooBarBazAF1Z'));
    }

    public function testUpperCaseUnderscoreNoTransform()
    {
        $propertyNaming = new DefaultPropertyNamingStrategy(PropertyNamingPolicy::UPPER_CASE_WITH_UNDERSCORES);

        self::assertSame('FOO', $propertyNaming->translateName('foo'));
    }

    public function testUpperCaseUnderscoreSimpleTransform()
    {
        $propertyNaming = new DefaultPropertyNamingStrategy(PropertyNamingPolicy::UPPER_CASE_WITH_UNDERSCORES);

        self::assertSame('FOO_BAR', $propertyNaming->translateName('fooBar'));
    }

    public function testUpperCaseUnderscoreComplex()
    {
        $propertyNaming = new DefaultPropertyNamingStrategy(PropertyNamingPolicy::UPPER_CASE_WITH_UNDERSCORES);

        self::assertSame('FOO_BAR_BAZ_A_F1_Z', $propertyNaming->translateName('FooBarBazAF1Z'));
    }

    public function testUpperCamelCaseNoTransform()
    {
        $propertyNaming = new DefaultPropertyNamingStrategy(PropertyNamingPolicy::UPPER_CAMEL_CASE);

        self::assertSame('Foo', $propertyNaming->translateName('foo'));
    }

    public function testUpperCamelCaseSimpleTransform()
    {
        $propertyNaming = new DefaultPropertyNamingStrategy(PropertyNamingPolicy::UPPER_CAMEL_CASE);

        self::assertSame('FooBar', $propertyNaming->translateName('fooBar'));
    }

    public function testUpperCamelCaseComplex()
    {
        $propertyNaming = new DefaultPropertyNamingStrategy(PropertyNamingPolicy::UPPER_CAMEL_CASE);

        self::assertSame('FooBarBazAF1Z', $propertyNaming->translateName('FooBarBazAF1Z'));
    }

    public function testUpperCamelCaseSpacesNoTransform()
    {
        $propertyNaming = new DefaultPropertyNamingStrategy(PropertyNamingPolicy::UPPER_CAMEL_CASE_WITH_SPACES);

        self::assertSame('Foo', $propertyNaming->translateName('foo'));
    }

    public function testUpperCamelCaseSpacesSimpleTransform()
    {
        $propertyNaming = new DefaultPropertyNamingStrategy(PropertyNamingPolicy::UPPER_CAMEL_CASE_WITH_SPACES);

        self::assertSame('Foo Bar', $propertyNaming->translateName('fooBar'));
    }

    public function testUpperCamelCaseSpacesComplex()
    {
        $propertyNaming = new DefaultPropertyNamingStrategy(PropertyNamingPolicy::UPPER_CAMEL_CASE_WITH_SPACES);

        self::assertSame('Foo Bar Baz A F1 Z', $propertyNaming->translateName('FooBarBazAF1Z'));
    }

    public function testInvalidPolicy()
    {
        try {
            (new DefaultPropertyNamingStrategy('foo'))->translateName('foo');
        } catch (\RuntimeException $exception) {
            self::assertSame('Gson: This property naming strategy is not supported', $exception->getMessage());
            return;
        }

        self::fail('Exception was not thrown');
    }
}
