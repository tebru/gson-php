<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
declare(strict_types=1);


namespace Tebru\Gson\Internal;

use LogicException;
use Tebru\Gson\JsonWritable;

/**
 * Class JsonWriter
 *
 * @author Nate Brunette <n@tebru.net>
 */
abstract class JsonWriter implements JsonWritable
{
    use JsonPath;

    /**
     * True if we should serialize nulls
     *
     * @var bool
     */
    protected $serializeNull = false;

    /**
     * Stack of values to be written
     *
     * @var array
     */
    protected $stack = [];

    /**
     * Size of the stack array
     *
     * @var int
     */
    protected $stackSize = 0;

    /**
     * A cache of the parsing state corresponding to the stack
     *
     * @var int[]
     */
    protected $stackStates = [];

    /**
     * When serializing an object, store the name that should be serialized
     *
     * @var
     */
    protected $pendingName;

    /**
     * The final result that will be json encoded
     *
     * @var mixed
     */
    protected $result;

    /**
     * Writes a property name
     *
     * @param string $name
     * @return JsonWritable
     * @throws LogicException
     */
    public function name(string $name): JsonWritable
    {
        if ($this->stackStates[$this->stackSize - 1] !== self::STATE_OBJECT_NAME) {
            $this->assertionFailed('Cannot call name() at this point.  Either name() has already been called or object serialization has not been started');
        }

        $this->pendingName = $name;
        $this->stackStates[$this->stackSize - 1] = self::STATE_OBJECT_VALUE;
        $this->pathNames[$this->pathIndex] = $name;

        return $this;
    }

    /**
     * Sets whether nulls are serialized
     *
     * @param bool $serializeNull
     * @return void
     */
    public function setSerializeNull(bool $serializeNull): void
    {
        $this->serializeNull = $serializeNull;
    }

    /**
     * Thrown in there's a logic error during serialization
     *
     * @param string $message
     * @throws \LogicException
     */
    protected function assertionFailed(string $message): void
    {
        throw new LogicException($message.\sprintf(' at "%s"', $this->getPath()));
    }
}
