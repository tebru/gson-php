<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock;

use Tebru\Gson\Annotation\SerializedName;

/**
 * Class AnnotatedMock
 *
 * @author Nate Brunette <n@tebru.net>
 */
class AnnotatedMock
{
    /**
     * @SerializedName("foobar")
     */
    private $fooBar;

    private $fooBarBaz;
}
