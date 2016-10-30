<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock\Annotation;

/**
 * Class MockAnnotation
 *
 * @author Nate Brunette <n@tebru.net>
 */
abstract class MockAnnotation
{
    private $values;

    public function __construct(array $values)
    {
        $this->values = $values;
    }
}
