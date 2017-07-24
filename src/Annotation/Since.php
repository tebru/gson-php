<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Annotation;

use Tebru\AnnotationReader\AbstractAnnotation;

/**
 * Class Since
 *
 * Used to exclude classes or properties that should not be serialized if the version number
 * is less than the @Since value.  Will not be used if version number is not defined on the
 * builder.
 *
 * @author Nate Brunette <n@tebru.net>
 *
 * @Annotation
 * @Target({"CLASS", "PROPERTY", "METHOD"})
 */
class Since extends AbstractAnnotation
{
    /**
     * @var string
     */
    private $value;

    /**
     * Initialize annotation data
     */
    protected function init(): void
    {
        $this->assertKey();

        $this->value = (string)$this->data['value'];
    }

    /**
     * Get the value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }


}
