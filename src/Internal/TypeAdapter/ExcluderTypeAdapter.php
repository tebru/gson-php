<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal\TypeAdapter;

use Tebru\Gson\Internal\JsonWritable;
use Tebru\Gson\Internal\PhpType;
use Tebru\Gson\Internal\TypeAdapter\Factory\ExcluderTypeAdapterFactory;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\JsonReadable;
use Tebru\Gson\TypeAdapter;

/**
 * Class ExcluderTypeAdapter
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class ExcluderTypeAdapter extends TypeAdapter
{
    /**
     * @var PhpType
     */
    private $phpType;

    /**
     * @var TypeAdapterProvider
     */
    private $typeAdapterProvider;

    /**
     * True if we're skipping serialization
     *
     * @var bool
     */
    private $skipSerialize;

    /**
     * True if we're skipping deserialization
     *
     * @var bool
     */
    private $skipDeserialize;

    /**
     * Constructor
     *
     * @param PhpType $phpType
     * @param TypeAdapterProvider $typeAdapterProvider
     * @param bool $skipSerialize
     * @param bool $skipDeserialize
     */
    public function __construct(PhpType $phpType, TypeAdapterProvider $typeAdapterProvider, bool $skipSerialize, bool $skipDeserialize)
    {
        $this->phpType = $phpType;
        $this->typeAdapterProvider = $typeAdapterProvider;
        $this->skipSerialize = $skipSerialize;
        $this->skipDeserialize = $skipDeserialize;
    }

    /**
     * Read the next value, convert it to its type and return it
     *
     * @param JsonReadable $reader
     * @return mixed
     * @throws \InvalidArgumentException if the type cannot be handled by a type adapter
     */
    public function read(JsonReadable $reader)
    {
        if ($this->skipDeserialize) {
            $reader->skipValue();

            return null;
        }

        $delegateAdapter = $this->typeAdapterProvider->getAdapter($this->phpType, ExcluderTypeAdapterFactory::class);

        return $delegateAdapter->read($reader);
    }

    /**
     * Write the value to the writer for the type
     *
     * @param JsonWritable $writer
     * @param mixed $value
     * @return void
     */
    public function write(JsonWritable $writer, $value): void
    {
    }
}
