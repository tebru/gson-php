<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal\TypeAdapter;

use Tebru\Gson\JsonWritable;
use Tebru\Gson\PhpType;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\JsonReadable;
use Tebru\Gson\TypeAdapter;
use Tebru\Gson\TypeAdapterFactory;

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
     * @var TypeAdapterFactory
     */
    private $skip;

    /**
     * Constructor
     *
     * @param PhpType $phpType
     * @param TypeAdapterProvider $typeAdapterProvider
     * @param bool $skipSerialize
     * @param bool $skipDeserialize
     * @param TypeAdapterFactory $skip
     */
    public function __construct(
        PhpType $phpType,
        TypeAdapterProvider $typeAdapterProvider,
        bool $skipSerialize,
        bool $skipDeserialize,
        TypeAdapterFactory $skip
    ) {
        $this->phpType = $phpType;
        $this->typeAdapterProvider = $typeAdapterProvider;
        $this->skipSerialize = $skipSerialize;
        $this->skipDeserialize = $skipDeserialize;
        $this->skip = $skip;
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

        $delegateAdapter = $this->typeAdapterProvider->getAdapter($this->phpType, $this->skip);

        return $delegateAdapter->read($reader);
    }

    /**
     * Write the value to the writer for the type
     *
     * @param JsonWritable $writer
     * @param mixed $value
     * @return void
     * @throws \InvalidArgumentException if the type cannot be handled by a type adapter
     */
    public function write(JsonWritable $writer, $value): void
    {
        if ($this->skipSerialize) {
            $writer->writeNull();

            return;
        }

        $delegateAdapter = $this->typeAdapterProvider->getAdapter($this->phpType, $this->skip);
        $delegateAdapter->write($writer, $value);
    }
}
