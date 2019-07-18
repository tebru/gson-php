<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Annotation;

use Tebru\AnnotationReader\AbstractAnnotation;
use Tebru\Gson\Exclusion\ExclusionStrategy;

/**
 * Class ExclusionCheck
 *
 * If the requireExclusionCheck setting is set on the builder, this annotation
 * will be required to run the [@see ExclusionStrategy]
 *
 * @author Nate Brunette <n@tebru.net>
 *
 * @Annotation
 * @Target({"CLASS", "PROPERTY"})
 */
class ExclusionCheck extends AbstractAnnotation
{
    /**
     * Initialize annotation data
     */
    protected function init(): void
    {
        // no value annotation
    }
}
