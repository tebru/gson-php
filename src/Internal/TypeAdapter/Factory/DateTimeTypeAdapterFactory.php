<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal\TypeAdapter\Factory;

use DateTimeInterface;
use Tebru\Gson\Internal\TypeAdapter\DateTimeTypeAdapter;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\PhpType;
use Tebru\Gson\TypeAdapter;
use Tebru\Gson\TypeAdapterFactory;

/**
 * Class DateTimeTypeAdapterFactory
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class DateTimeTypeAdapterFactory implements TypeAdapterFactory
{
    /**
     * @var string
     */
    private $format;

    /**
     * Constructor
     *
     * @param string $format
     */
    public function __construct(string $format)
    {
        $this->format = $format;
    }

    /**
     * Will be called before ::create() is called.  The current type will be passed
     * in.  Return false if ::create() should not be called.
     *
     * @param PhpType $type
     * @return bool
     */
    public function supports(PhpType $type): bool
    {
        if (!$type->isObject()) {
            return false;
        }

        return $type->isA(DateTimeInterface::class);
    }

    /**
     * Accepts the current type and a [@see TypeAdapterProvider] in case another type adapter needs
     * to be fetched during creation.  Should return a new instance of the TypeAdapter.
     *
     * @param PhpType $type
     * @param TypeAdapterProvider $typeAdapterProvider
     * @return TypeAdapter
     */
    public function create(PhpType $type, TypeAdapterProvider $typeAdapterProvider): TypeAdapter
    {
        return new DateTimeTypeAdapter($type, $this->format);
    }
}
