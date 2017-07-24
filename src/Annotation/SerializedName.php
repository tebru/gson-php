<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Annotation;

use Tebru\AnnotationReader\AbstractAnnotation;

/**
 * Class SerializedName
 *
 * Used to define the name that should appear in json.  This annotation will override
 * any PropertyNamingStrategy.
 *
 * @author Nate Brunette <n@tebru.net>
 *
 * @Annotation
 * @Target({"PROPERTY", "METHOD"})
 */
class SerializedName extends AbstractAnnotation
{
}
