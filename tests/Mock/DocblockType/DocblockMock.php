<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Test\Mock\DocblockType;

use ArrayObject;
use Tebru\Gson\Test\Mock\ChildClass;
use Tebru\Gson\Test\Mock\ChildClassParent as Aliased;
use Tebru\Gson\Test\Mock\DocblockType\DocblockAliasable as Aliased2;

use Tebru\Gson\Test\Mock\{ChildClassParent2};

use Tebru\Gson\Test\Mock\{
    ClassWithoutParent,
    ClassWithParameters as AliasedGroup,
    ClassWithParametersInstanceCreator
};

/**
 * Class DocblockTestClass
 *
 * @author Nate Brunette <n@tebru.net>
 */
class DocblockMock
{
    /**
     * @var int
     */
    private $scalar;

    /**
     * @var null|string
     */
    private $nullableScalar;

    /**
     * @var float|NULL
     */
    private $nullableScalar2;

    /**
     * @var mixed
     */
    private $mixed;

    /**
     * @var float|null|int
     */
    private $multipleTypes;

    /**
     * @var float|int
     */
    private $multipleTypes2;

    /**
     * @var DocblockFoo|null
     */
    private $classSameNamespace;

    /**
     * @var ChildClass
     */
    private $classImported;

    /**
     * @var \Tebru\Gson\Test\Mock\ChildClass
     */
    private $classFullName;

    /**
     * @var Aliased
     */
    private $classAliased;

    /**
     * @var Aliased2
     */
    private $classSameNamespaceAliased;

    /**
     * @var ChildClassParent2
     */
    private $classGroupOneLine;

    /**
     * @var ClassWithoutParent
     */
    private $classGroupMultipleLines1;

    /**
     * @var ClassWithParametersInstanceCreator
     */
    private $classGroupMultipleLines2;

    /**
     * @var AliasedGroup
     */
    private $classGroupMultipleLinesAliased;

    /**
     * @var ArrayObject
     */
    private $classGlobal;

    /**
     * @var array
     */
    private $array;

    /**
     * @var int[]
     */
    private $typedArray;

    /**
     * @var mixed[][]
     */
    private $nestedArray;

    /**
     * @var DocblockFoo[]
     */
    private $classArray;

    /**
     * @var null
     */
    private $onlyNull;

    /**
     * @var
     */
    private $noTypes;

    /**
     * @return int
     */
    public function getFoo()
    {
        return 1;
    }

    /**
     * @param int $var
     */
    public function setFoo($var): void
    {
    }
}
