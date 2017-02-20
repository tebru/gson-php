<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock;

use Tebru\Gson\Annotation as Gson;

/**
 * Class PropertyCollectionMock
 *
 * @author Nate Brunette <n@tebru.net>
 */
class PropertyCollectionMock
{
    /**
     * @Gson\Accessor(get="getChanged", set="setChanged")
     */
    private $changedAccessors;

    /**
     * @Gson\SerializedName("changedname")
     */
    public $changedName;

    /**
     * @Gson\Exclude()
     */
    private $exclude;

    /**
     * @Gson\Type("int")
     */
    private $type;

    public function getChanged(): ?bool
    {
        return $this->changedAccessors;
    }

    public function setChanged(bool $changed)
    {
        $this->changedAccessors = $changed;
    }

    /**
     * @Gson\VirtualProperty()
     * @Gson\SerializedName("new_virtual_property")
     */
    public function virtualProperty(): string
    {
        return 'foo'.'bar';
    }

    /**
     * @Gson\VirtualProperty()
     * @Gson\Exclude
     */
    public function virtualProperty2(): string
    {
        return 'foo'.'bar';
    }
}
