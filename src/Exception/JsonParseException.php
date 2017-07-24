<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Exception;

use RuntimeException;

/**
 * Class JsonParseException
 *
 * Thrown when there is an issue parsing the json string
 *
 * @author Nate Brunette <n@tebru.net>
 */
class JsonParseException extends RuntimeException
{
}
