<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Exception;

use JMS\Serializer\Exception\RuntimeException;
use Throwable;

/**
 * Class UnsupportedMethodException
 *
 * @author Nate Brunette <n@tebru.net>
 */
class UnsupportedMethodException extends RuntimeException implements Throwable
{
}
