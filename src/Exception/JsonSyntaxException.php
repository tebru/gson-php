<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Exception;

/**
 * Class UnexpectedJsonTokenException
 *
 * Thrown when a malformed type is found
 *
 * @author Nate Brunette <n@tebru.net>
 */
class JsonSyntaxException extends JsonParseException
{
}
