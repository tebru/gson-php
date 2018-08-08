<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Exception;

/**
 * Class JsonDecodeException
 *
 * Thrown when there is an issue decoding the json string
 *
 * @author Nate Brunette <n@tebru.net>
 */
class JsonDecodeException extends JsonParseException
{
    /**
     * The json payload that failed to decode
     *
     * @var mixed
     */
    private $payload;

    /**
     * Constructor
     *
     * @param string $lastErrorMessage The json error message
     * @param int $code The json error code
     * @param mixed $payload The payload that failed to decode
     */
    public function __construct(string $lastErrorMessage, int $code, $payload)
    {
        $message = \sprintf('Could not decode json, the error message was: "%s"', $lastErrorMessage);

        parent::__construct($message, $code);

        $this->payload = $payload;
    }

    /**
     * Get the json payload that failed to decode
     *
     * @return mixed
     */
    public function getPayload()
    {
        return $this->payload;
    }
}
