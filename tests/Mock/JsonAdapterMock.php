<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock;

use Tebru\Gson\Annotation\JsonAdapter;

/**
 * Class JsonAdapterMock
 *
 * @author Nate Brunette <n@tebru.net>
 */
class JsonAdapterMock
{
    /**
     * @JsonAdapter("Tebru\Gson\Internal\TypeAdapter\StringTypeAdapter")
     */
    private $foo;
}
