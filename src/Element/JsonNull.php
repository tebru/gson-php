<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Element;

/**
 * Class JsonNull
 *
 * Represents json null
 *
 * @author Nate Brunette <n@tebru.net>
 */
class JsonNull extends JsonElement
{
    /**
     * Specify data which should be serialized to JSON
     *
     * @return void
     */
    public function jsonSerialize(): void
    {
    }
}
