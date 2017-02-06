<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal;

use Iterator;
use SplQueue;
use Tebru\Gson\Element\JsonObject;

/**
 * Class JsonObjectIterator
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class JsonObjectIterator implements Iterator
{
    /**
     * @var SplQueue
     */
    private $queue;

    /**
     * Constructor
     *
     * @param JsonObject $jsonObject
     */
    public function __construct(JsonObject $jsonObject)
    {
        $this->queue = new SplQueue();
        foreach($jsonObject as $key => $value) {
            $this->queue->enqueue([$key, $value]);
        }

        $this->queue->rewind();
    }

    /**
     * Return the current element
     *
     * @return array
     */
    public function current(): array
    {
        return $this->queue->current();
    }

    /**
     * Move forward to next element
     *
     * @return void
     */
    public function next(): void
    {
        $this->queue->next();
    }

    /**
     * Return the key of the current element
     *
     * @return string
     */
    public function key(): string
    {
        return $this->queue->current()[0];
    }

    /**
     * Checks if current position is valid
     *
     * @return bool
     */
    public function valid(): bool
    {
        return $this->queue->valid();
    }

    /**
     * Rewind the Iterator to the first element
     *
     * @return void
     */
    public function rewind(): void
    {
        $this->queue->rewind();
    }
}
