<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal\TypeAdapter\Factory;

use Tebru\Gson\Internal\Excluder;
use Tebru\Gson\Internal\MetadataFactory;
use Tebru\Gson\Internal\TypeAdapter\ExcluderTypeAdapter;
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\TypeAdapter;
use Tebru\Gson\TypeAdapterFactory;
use Tebru\PhpType\TypeToken;

/**
 * Class ExcluderTypeAdapterFactory
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class ExcluderTypeAdapterFactory implements TypeAdapterFactory
{
    /**
     * @var Excluder
     */
    private $excluder;

    /**
     * @var MetadataFactory
     */
    private $metadataFactory;

    /**
     * Constructor
     *
     * @param Excluder $excluder
     * @param MetadataFactory $metadataFactory
     */
    public function __construct(Excluder $excluder, MetadataFactory $metadataFactory)
    {
        $this->excluder = $excluder;
        $this->metadataFactory = $metadataFactory;
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
        if (!$type->isObject()) {
            return false;
        }

        if (!\class_exists($type->getRawType())) {
            return false;
        }

        $classMetadata = $this->metadataFactory->createClassMetadata($type->getRawType());
        $skipSerialize = $this->excluder->excludeClass($classMetadata, true);
        $skipDeserialize = $this->excluder->excludeClass($classMetadata, false);

        // use this type adapter if we're skipping serialization or deserialization
        return $skipSerialize || $skipDeserialize;
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
        $classMetadata = $this->metadataFactory->createClassMetadata($type->getRawType());
        $skipSerialize = $this->excluder->excludeClass($classMetadata, true);
        $skipDeserialize = $this->excluder->excludeClass($classMetadata, false);

        return new ExcluderTypeAdapter($type, $typeAdapterProvider, $skipSerialize, $skipDeserialize, $this);
    }
}
