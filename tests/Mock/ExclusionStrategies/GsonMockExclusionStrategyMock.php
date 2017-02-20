<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock\ExclusionStrategies;

use Tebru\Gson\ClassMetadata;
use Tebru\Gson\ExclusionStrategy;
use Tebru\Gson\PropertyMetadata;
use Tebru\Gson\Test\Mock\GsonMock;

/**
 * Class GsonMockExclusionStrategyMock
 *
 * @author Nate Brunette <n@tebru.net>
 */
class GsonMockExclusionStrategyMock implements ExclusionStrategy
{
    public $skipProperty = true;
    /**
     * Return true if the class should be ignored
     *
     * @param ClassMetadata $classMetadata
     * @return bool
     */
    public function shouldSkipClass(ClassMetadata $classMetadata): bool
    {
        return false;
    }

    /**
     * Return true if the property should be ignored
     *
     * @param PropertyMetadata $propertyMetadata
     * @return bool
     */
    public function shouldSkipProperty(PropertyMetadata $propertyMetadata): bool
    {
        if (false === $this->skipProperty) {
            return false;
        }

        return $propertyMetadata->getDeclaringClassName() === GsonMock::class
            && $propertyMetadata->getName() === 'excludeFromStrategy';
    }
}
