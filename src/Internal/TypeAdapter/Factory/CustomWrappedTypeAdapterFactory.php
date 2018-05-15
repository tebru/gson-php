<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal\TypeAdapter\Factory;

use Tebru\Gson\Internal\TypeAdapter\CustomWrappedTypeAdapter;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\JsonDeserializer;
use Tebru\Gson\JsonSerializer;
use Tebru\Gson\TypeAdapter;
use Tebru\Gson\TypeAdapterFactory;
use Tebru\PhpType\TypeToken;

/**
 * Class CustomWrappedTypeAdapterFactory
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class CustomWrappedTypeAdapterFactory implements TypeAdapterFactory
{
    /**
     * @var TypeToken
     */
    private $type;

    /**
     * @var JsonSerializer
     */
    private $serializer;

    /**
     * @var JsonDeserializer
     */
    private $deserializer;

    /**
     * @var bool
     */
    private $strict;

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
     * Will be called before ::create() is called.  The current type will be passed
     * in.  Return false if ::create() should not be called.
     *
     * @param TypeToken $type
     * @return bool
     */
    public function supports(TypeToken $type): bool
    {
        return $this->strict
            ? $type->getRawType() === $this->type->getRawType()
            : $type->isA($this->type->getRawType());
    }

    /**
     * Accepts the current type and a [@see TypeAdapterProvider] in case another type adapter needs
     * to be fetched during creation.  Should return a new instance of the TypeAdapter.
     *
     * @param TypeToken $type
     * @param TypeAdapterProvider $typeAdapterProvider
     * @return TypeAdapter
     */
    public function create(TypeToken $type, TypeAdapterProvider $typeAdapterProvider): TypeAdapter
    {
        return new CustomWrappedTypeAdapter($type, $typeAdapterProvider, $this->serializer, $this->deserializer, $this);
    }
}
