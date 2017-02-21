<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Mock\Unit\Internal\Data\AnnotationCollectionFactoryTest;

use Tebru\Gson\Annotation as Gson;

/**
 * Class AnnotationCollectionFactoryTestParentMock
 *
 * @author Nate Brunette <n@tebru.net>
 *
 * @Gson\Since("1")
 * @Gson\Until("3")
 */
class AnnotationCollectionFactoryTestParentMock
{
    /**
     * @Gson\Type("int")
     * @Gson\SerializedName("no_parents")
     */
    private $noParents;

    /**
     * @Gson\Type("string")
     * @Gson\Since("1")
     */
    private $withParent;

    /**
     * @Gson\VirtualProperty()
     * @Gson\SerializedName("method_1")
     */
    public function method1()
    {
    }

    /**
     * @Gson\VirtualProperty()
     * @Gson\SerializedName("method2")
     * @Gson\Type("int")
     */
    public function method2()
    {
    }
}
