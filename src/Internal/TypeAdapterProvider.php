<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal;

use InvalidArgumentException;
use Tebru\Gson\TypeAdapter;
use Tebru\Gson\TypeAdapterFactory;

/**
 * Class TypeAdapterProvider
 *
 * @author Nate Brunette <n@tebru.net>
 */
class TypeAdapterProvider
{
    /**
     * A cache of created type adapters
     *
     * @var array
     */
    private $typeAdapters = [];

    /**
     * All registered [@see TypeAdapter]s
     *
     * @var TypeAdapterFactory[]
     */
    private $typeAdapterFactories;

    /**
     * Constructor
     *
     * @param array $typeAdapterFactories
     */
    public function __construct(array $typeAdapterFactories)
    {
        $this->typeAdapterFactories = $typeAdapterFactories;
    }

    /**
     * Creates a key based on the type, and optionally the class that should be skipped.
     * Returns the [@see TypeAdapter] if it has already been created, otherwise loops
     * over all of the factories and finds a type adapter that supports the type.
     *
     * @param PhpType $type
     * @param string $skipClass
     * @return TypeAdapter
     * @throws \InvalidArgumentException if the type cannot be handled by a type adapter
     */
    public function getAdapter(PhpType $type, string $skipClass = null): TypeAdapter
    {
        $key = (string) $type . ':' . (string) $skipClass;
        if (array_key_exists($key, $this->typeAdapters)) {
            return $this->typeAdapters[$key];
        }

        foreach ($this->typeAdapterFactories as $typeAdapterFactory) {
            if (get_class($typeAdapterFactory) === $skipClass) {
                continue;
            }

            if (!$typeAdapterFactory->supports($type)) {
                continue;
            }

            $typeAdapter = $typeAdapterFactory->create($type, $this);
            $this->typeAdapters[$key] = $typeAdapter;

            return $typeAdapter;
        }

        throw new InvalidArgumentException(sprintf(
            'The type "%s" could not be handled by any of the registered type adapters',
            (string) $type
        ));
    }
}
