<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal;

use stdClass;

/**
 * Class StdClassIterator
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class StdClassIterator extends AbstractIterator
{
    /**
     * Constructor
     *
     * @param stdClass $class
     */
    public function __construct(stdClass $class)
    {
        $vars = \get_object_vars($class);
        foreach ($vars as $key => $var) {
            $this->queue[] = [$key, $var];
            $this->total++;
        }
    }
}
