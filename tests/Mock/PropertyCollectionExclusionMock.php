<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock;

use Tebru\Gson\Annotation\Type;
use Tebru\Gson\Test\Mock\ExcluderVersionMock;

/**
 * Class PropertyCollectionExclusionMock
 *
 * @author Nate Brunette <n@tebru.net>
 */
class PropertyCollectionExclusionMock
{
    /**
     * @Type("Tebru\Gson\Test\Mock\ExcluderVersionMock")
     */
    private $excluderVersionMock;
    public $foo;
}
