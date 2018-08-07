<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal;

/**
 * Trait JsonPath
 *
 * Used during reading/writing to keep track of the current path
 *
 * @author Nate Brunette <n@tebru.net>
 */
trait JsonPath
{
    /**
     * An array of path names that correspond to the current stack
     *
     * @var array
     */
    protected $pathNames = [];

    /**
     * An array of path indices that correspond to the current stack. This array could contain invalid
     * values at indexes outside the current stack. It could also contain incorrect values at indexes
     * where a path name is used. Data should only be fetched by referencing the $pathIndex
     *
     * @var int[]
     */
    protected $pathIndices = [-1];

    /**
     * The current path index corresponding to the pathIndices array
     *
     * @var int
     */
    protected $pathIndex = 0;

    /**
     * Get the current read path in json xpath format
     *
     * @return string
     */
    public function getPath(): string
    {
        $result = ['$'];

        for ($index = 1; $index <= $this->pathIndex; $index++) {
            if (!empty($this->pathNames[$index])) {
                $result[] .= '.'.$this->pathNames[$index];
                continue;
            }

            // skip initial value
            if ($this->pathIndices[$index] === -1) {
                continue;
            }

            $result[] .= '['.$this->pathIndices[$index].']';
        }

        return implode($result);
    }
}
