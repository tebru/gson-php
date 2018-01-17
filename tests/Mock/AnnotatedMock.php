<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock;

use Tebru\Gson\Annotation as Gson;
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

    /**
     * @Gson\VirtualProperty("vfoo")
     */
    public function virtualFoo() {}

    /**
     * @SerializedName("vfooOverride")
     * @Gson\VirtualProperty("vfoo")
     */
    public function virtualFooWithSerializedName() {}
}
