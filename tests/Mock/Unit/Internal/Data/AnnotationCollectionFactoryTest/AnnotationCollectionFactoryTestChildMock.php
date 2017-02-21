<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock\Unit\Internal\Data\AnnotationCollectionFactoryTest;

use Tebru\Gson\Annotation as Gson;

/**
 * Class AnnotationCollectionFactoryTestChildMock
 *
 * @author Nate Brunette <n@tebru.net>
 *
 * @Gson\Since("2")
 */
class AnnotationCollectionFactoryTestChildMock extends AnnotationCollectionFactoryTestParentMock
{
    /**
     * @Gson\Type("int")
     * @Gson\SerializedName("with_parents")
     */
    private $withParent;

    /**
     * @Gson\VirtualProperty()
     * @Gson\SerializedName("method_2")
     */
    public function method2()
    {
    }
}
