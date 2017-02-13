<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock;

use Tebru\Gson\Annotation\JsonAdapter;

/**
 * Class JsonAdapterClassMock
 *
 * @author Nate Brunette <n@tebru.net>
 *
 * @JsonAdapter("Tebru\Gson\Internal\TypeAdapter\StringTypeAdapter")
 */
class JsonAdapterClassMock
{
    private $foo;
}
