<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Annotation;

use Tebru\AnnotationReader\AbstractAnnotation;

/**
 * Class Until
 *
 * Used to exclude classes or properties that should not be serialized if the version number
 * is greater than or equal to the @Until value.  Will not be used if version number is not
 * defined on the builder.
 *
 * @author Nate Brunette <n@tebru.net>
 *
 * @Annotation
 * @Target({"CLASS", "PROPERTY", "METHOD"})
 */
class Until extends AbstractAnnotation
{
    /**
     * Initialize annotation data
     */
    protected function init(): void
    {
        $this->value = (string)$this->getValue();
    }
}
