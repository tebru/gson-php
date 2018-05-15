<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Test\Unit\Internal;

use PHPUnit_Framework_TestCase;
use Tebru\Gson\Gson;
use Tebru\Gson\Test\Mock\Polymorphic\Base;
use Tebru\Gson\Test\Mock\Polymorphic\PolymorphicChild1;
use Tebru\Gson\Test\Mock\Polymorphic\PolymorphicChild2;
use Tebru\Gson\Test\Mock\Polymorphic\PolymorphicDiscriminator;

/**
 * Class PolymorphicDeserializationTest
 *
 * @author Nate Brunette <n@tebru.net>
 */
class PolymorphicDeserializationTest extends PHPUnit_Framework_TestCase
{
    public function testCanDeserializeBasedOnProperty()
    {
        $gson = Gson::builder()
            ->addDiscriminator(Base::class, new PolymorphicDiscriminator())
            ->build();

        $object = $gson->fromJson('{"status": "foo"}', Base::class);

        self::assertInstanceOf(PolymorphicChild1::class, $object);
    }

    public function testCanDeserializeBasedOnPropertySwitch()
    {
        $gson = Gson::builder()
            ->addDiscriminator(Base::class, new PolymorphicDiscriminator())
            ->build();

        $object = $gson->fromJson('{"status": "bar"}', Base::class);

        self::assertInstanceOf(PolymorphicChild2::class, $object);
    }

    public function testCanDeserializeNested()
    {
        $gson = Gson::builder()
            ->addDiscriminator(Base::class, new PolymorphicDiscriminator())
            ->build();

        $object = $gson->fromJson('{"status": "foo", "nested": {"status": "bar"}}', Base::class);

        self::assertInstanceOf(PolymorphicChild1::class, $object);
        self::assertInstanceOf(PolymorphicChild2::class, $object->getNested());
    }
}
