<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);


namespace Tebru\Gson\Test\Mock;

use Tebru\Gson\Annotation as Gson;

/**
 * @Gson\Exclude()
 */
class GsonMockChild extends GsonMock
{
    /**
     * @Gson\Expose()
     *
     * @var int
     */
    public $id;

    /**
     * @var bool
     */
    public $excluded;
}
