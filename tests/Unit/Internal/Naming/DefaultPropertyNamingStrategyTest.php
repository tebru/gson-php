<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\Naming;

use PHPUnit\Framework\TestCase;
use Tebru\Gson\Internal\Naming\DefaultPropertyNamingStrategy;
use Tebru\Gson\PropertyNamingPolicy;

/**
 * Class DefaultPropertyNamingStrategyTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\Naming\DefaultPropertyNamingStrategy
 */
class DefaultPropertyNamingStrategyTest extends TestCase
{
    public function testIdentity(): void
    {
        $propertyNaming = new DefaultPropertyNamingStrategy(PropertyNamingPolicy::IDENTITY);

        self::assertSame('foo', $propertyNaming->translateName('foo'));
    }

    public function testIdentityComplex(): void
    {
        $propertyNaming = new DefaultPropertyNamingStrategy(PropertyNamingPolicy::IDENTITY);

        self::assertSame('FooBarBazAF1Z', $propertyNaming->translateName('FooBarBazAF1Z'));
    }

    public function testLowerCaseDashesNoTransform(): void
    {
        $propertyNaming = new DefaultPropertyNamingStrategy(PropertyNamingPolicy::LOWER_CASE_WITH_DASHES);

        self::assertSame('foo', $propertyNaming->translateName('foo'));
    }

    public function testLowerCaseDashesSimpleTransform(): void
    {
        $propertyNaming = new DefaultPropertyNamingStrategy(PropertyNamingPolicy::LOWER_CASE_WITH_DASHES);

        self::assertSame('foo-bar', $propertyNaming->translateName('fooBar'));
    }

    public function testLowerCaseDashesComplexTransform(): void
    {
        $propertyNaming = new DefaultPropertyNamingStrategy(PropertyNamingPolicy::LOWER_CASE_WITH_DASHES);

        self::assertSame('foo-bar-baz-a-f1-z', $propertyNaming->translateName('FooBarBazAF1Z'));
    }

    public function testLowerCaseUnderscoreNoTransform(): void
    {
        $propertyNaming = new DefaultPropertyNamingStrategy(PropertyNamingPolicy::LOWER_CASE_WITH_UNDERSCORES);

        self::assertSame('foo', $propertyNaming->translateName('foo'));
    }

    public function testLowerCaseUnderscoreSimpleTransform(): void
    {
        $propertyNaming = new DefaultPropertyNamingStrategy(PropertyNamingPolicy::LOWER_CASE_WITH_UNDERSCORES);

        self::assertSame('foo_bar', $propertyNaming->translateName('fooBar'));
    }

    public function testLowerCaseUnderscoreComplex(): void
    {
        $propertyNaming = new DefaultPropertyNamingStrategy(PropertyNamingPolicy::LOWER_CASE_WITH_UNDERSCORES);

        self::assertSame('foo_bar_baz_a_f1_z', $propertyNaming->translateName('FooBarBazAF1Z'));
    }
    #

    public function testUpperCaseDashesNoTransform(): void
    {
        $propertyNaming = new DefaultPropertyNamingStrategy(PropertyNamingPolicy::UPPER_CASE_WITH_DASHES);

        self::assertSame('FOO', $propertyNaming->translateName('foo'));
    }

    public function testUpperCaseDashesSimpleTransform(): void
    {
        $propertyNaming = new DefaultPropertyNamingStrategy(PropertyNamingPolicy::UPPER_CASE_WITH_DASHES);

        self::assertSame('FOO-BAR', $propertyNaming->translateName('fooBar'));
    }

    public function testUpperCaseDashesComplexTransform(): void
    {
        $propertyNaming = new DefaultPropertyNamingStrategy(PropertyNamingPolicy::UPPER_CASE_WITH_DASHES);

        self::assertSame('FOO-BAR-BAZ-A-F1-Z', $propertyNaming->translateName('FooBarBazAF1Z'));
    }

    public function testUpperCaseUnderscoreNoTransform(): void
    {
        $propertyNaming = new DefaultPropertyNamingStrategy(PropertyNamingPolicy::UPPER_CASE_WITH_UNDERSCORES);

        self::assertSame('FOO', $propertyNaming->translateName('foo'));
    }

    public function testUpperCaseUnderscoreSimpleTransform(): void
    {
        $propertyNaming = new DefaultPropertyNamingStrategy(PropertyNamingPolicy::UPPER_CASE_WITH_UNDERSCORES);

        self::assertSame('FOO_BAR', $propertyNaming->translateName('fooBar'));
    }

    public function testUpperCaseUnderscoreComplex(): void
    {
        $propertyNaming = new DefaultPropertyNamingStrategy(PropertyNamingPolicy::UPPER_CASE_WITH_UNDERSCORES);

        self::assertSame('FOO_BAR_BAZ_A_F1_Z', $propertyNaming->translateName('FooBarBazAF1Z'));
    }

    public function testUpperCamelCaseNoTransform(): void
    {
        $propertyNaming = new DefaultPropertyNamingStrategy(PropertyNamingPolicy::UPPER_CAMEL_CASE);

        self::assertSame('Foo', $propertyNaming->translateName('foo'));
    }

    public function testUpperCamelCaseSimpleTransform(): void
    {
        $propertyNaming = new DefaultPropertyNamingStrategy(PropertyNamingPolicy::UPPER_CAMEL_CASE);

        self::assertSame('FooBar', $propertyNaming->translateName('fooBar'));
    }

    public function testUpperCamelCaseComplex(): void
    {
        $propertyNaming = new DefaultPropertyNamingStrategy(PropertyNamingPolicy::UPPER_CAMEL_CASE);

        self::assertSame('FooBarBazAF1Z', $propertyNaming->translateName('FooBarBazAF1Z'));
    }

    public function testUpperCamelCaseSpacesNoTransform(): void
    {
        $propertyNaming = new DefaultPropertyNamingStrategy(PropertyNamingPolicy::UPPER_CAMEL_CASE_WITH_SPACES);

        self::assertSame('Foo', $propertyNaming->translateName('foo'));
    }

    public function testUpperCamelCaseSpacesSimpleTransform(): void
    {
        $propertyNaming = new DefaultPropertyNamingStrategy(PropertyNamingPolicy::UPPER_CAMEL_CASE_WITH_SPACES);

        self::assertSame('Foo Bar', $propertyNaming->translateName('fooBar'));
    }

    public function testUpperCamelCaseSpacesComplex(): void
    {
        $propertyNaming = new DefaultPropertyNamingStrategy(PropertyNamingPolicy::UPPER_CAMEL_CASE_WITH_SPACES);

        self::assertSame('Foo Bar Baz A F1 Z', $propertyNaming->translateName('FooBarBazAF1Z'));
    }

    public function testInvalidPolicy(): void
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
