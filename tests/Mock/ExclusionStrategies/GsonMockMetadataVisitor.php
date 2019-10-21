<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock\ExclusionStrategies;

use Tebru\Gson\ClassMetadata;
use Tebru\Gson\ClassMetadataVisitor;

/**
 * Class GsonMockExclusionStrategyMock
 *
 * @author Nate Brunette <n@tebru.net>
 */
class GsonMockMetadataVisitor implements ClassMetadataVisitor
{
    public $skipProperty = true;

    /**
     * Handle the class or property metadata
     *
     * @param ClassMetadata $classMetadata
     */
    public function onLoaded(ClassMetadata $classMetadata): void
    {
        $property = $classMetadata->getProperty('excludeFromStrategy');
        if ($property === null) {
            return;
        }

        $property->setSkipSerialize(true)->setSkipDeserialize(true);
    }
}
