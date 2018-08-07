<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock\ExclusionStrategies;

use Tebru\Gson\ClassMetadata;
use Tebru\Gson\Exclusion\ClassDeserializationExclusionStrategy;
use Tebru\Gson\Test\Mock\Foo;

/**
 * Class FooDeserializationExclusionStrategy
 *
 * @author Nate Brunette <n@tebru.net>
 */
class FooDeserializationExclusionStrategy implements ClassDeserializationExclusionStrategy
{
    /**
     * Returns true if the class should be skipped during deserialization
     *
     * @param ClassMetadata $class
     * @return bool
     */
    public function skipDeserializingClass(ClassMetadata $class): bool
    {
        return Foo::class === $class->getName();
    }

    /**
     * Return true if the result of the strategy should be cached
     *
     * @return bool
     */
    public function shouldCache(): bool
    {
        return false;
    }
}
