<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Exception;

use RuntimeException;
use Tebru\Gson\JsonToken;

/**
 * Class UnexpectedJsonTokenException
 *
 * Thrown when an unexpected [@see JsonToken] is found
 *
 * @author Nate Brunette <n@tebru.net>
 */
class UnexpectedJsonTokenException extends RuntimeException
{
}
