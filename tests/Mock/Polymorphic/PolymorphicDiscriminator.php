<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */
declare(strict_types=1);


namespace Tebru\Gson\Test\Mock\Polymorphic;

use Tebru\Gson\Discriminator;
use Tebru\Gson\Element\JsonObject;

/**
 * Class PolymorphicDiscriminator
 *
 * @author Nate Brunette <n@tebru.net>
 */
class PolymorphicDiscriminator implements Discriminator
{
    /**
     * Returns a classname based on data provided in a [@see JsonObject]
     *
     * @param JsonObject $object
     * @return string
     */
    public function getClass(JsonObject $object): string
    {
        switch ($object->getAsString('status')) {
            case 'foo':
                return PolymorphicChild1::class;
            case 'bar':
                return PolymorphicChild2::class;
        }
    }
}
