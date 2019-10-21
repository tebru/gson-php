<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\TypeAdapter\Factory;

use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\JsonDeserializer;
use Tebru\Gson\JsonSerializer;
use Tebru\Gson\TypeAdapter;
use Tebru\Gson\TypeAdapter\CustomWrappedTypeAdapter;
use Tebru\Gson\TypeAdapterFactory;
use Tebru\PhpType\TypeToken;

/**
 * Class CustomWrappedTypeAdapterFactory
 *
 * @author Nate Brunette <n@tebru.net>
 */
class CustomWrappedTypeAdapterFactory implements TypeAdapterFactory
{
    /**
     * @var TypeToken
     */
    protected $type;

    /**
     * @var JsonSerializer
     */
    protected $serializer;

    /**
     * @var JsonDeserializer
     */
    protected $deserializer;

    /**
     * @var bool
     */
    protected $strict;

    /**
     * Constructor
     *
     * @param TypeToken $type
     * @param bool $strict
     * @param JsonSerializer|null $serializer
     * @param JsonDeserializer|null $deserializer
     */
    public function __construct(
        TypeToken $type,
        bool $strict,
        JsonSerializer $serializer = null,
        JsonDeserializer $deserializer = null
    ) {
        $this->type = $type;
        $this->serializer = $serializer;
        $this->deserializer = $deserializer;
        $this->strict = $strict;
    }

    /**
     * Accepts the current type and a [@see TypeAdapterProvider] in case another type adapter needs
     * to be fetched during creation.  Should return a new instance of the TypeAdapter. Will return
     * null if the type adapter is not supported for the provided type.
     *
     * @param TypeToken $type
     * @param TypeAdapterProvider $typeAdapterProvider
     * @return TypeAdapter|null
     */
    public function create(TypeToken $type, TypeAdapterProvider $typeAdapterProvider): ?TypeAdapter
    {
        if ($this->strict) {
            return $type->rawType === $this->type->rawType
                ? new CustomWrappedTypeAdapter($type, $typeAdapterProvider, $this->serializer, $this->deserializer, $this)
                : null;
        }

        return $type->isA($this->type->rawType)
            ? new CustomWrappedTypeAdapter($type, $typeAdapterProvider, $this->serializer, $this->deserializer, $this)
            : null;
    }
}
