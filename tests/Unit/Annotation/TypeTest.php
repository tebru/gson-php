<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Annotation;

use DateTime;
use LogicException;
use PHPUnit_Framework_TestCase;
use Tebru\Gson\Annotation\Type;

/**
 * Class TypeTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Annotation\Type
 */
class TypeTest extends PHPUnit_Framework_TestCase
{
    public function testCreateTypeAnnotation()
    {
        $type = new Type(['value' => 'Foo']);

        self::assertSame('Foo', (string) $type->getType());
    }

    public function testCreateTypeAnnotationWithOptions()
    {
        $type = new Type(['value' => DateTime::class, 'options' => ['format' => DateTime::ATOM]]);
        $phpType = $type->getType();

        self::assertSame(DateTime::class, (string) $phpType);
        self::assertSame(DateTime::ATOM, $phpType->getOptions()['format']);
    }

    public function testCreateThrowsException()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('@Type annotation must specify a type as the first argument');

        new Type([]);
    }
}
