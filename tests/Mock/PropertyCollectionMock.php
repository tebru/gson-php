<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock;

use Tebru\Gson\Annotation\Accessor;
use Tebru\Gson\Annotation\SerializedName;
use Tebru\Gson\Annotation\Type;
use Tebru\Gson\Annotation\VirtualProperty;

/**
 * Class PropertyCollectionMock
 *
 * @author Nate Brunette <n@tebru.net>
 */
class PropertyCollectionMock
{
    /**
     * @Accessor(get="getChanged", set="setChanged")
     */
    private $changedAccessors;

    /**
     * @SerializedName("changedname")
     */
    public $changedName;

    /**
     * @Type("int")
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
     * @VirtualProperty()
     * @SerializedName("new_virtual_property")
     */
    public function virtualProperty(): string
    {
        return 'foo'.'bar';
    }
}
