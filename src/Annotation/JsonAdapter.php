<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Annotation;

use Tebru\AnnotationReader\AbstractAnnotation;

/**
 * Class JsonAdapter
 *
 * Use this annotation to define a custom TypeAdapter for a class or property.  This annotation
 * cannot be used if the type of a property is ambiguous.  For example, if defined on a scalar
 * property that doesn't define an @Type annotation or provide an accessor with a type hint.
 *
 * The type adapter class should not be defined with any constructor arguments
 *
 * This annotation can point to a TypeAdapter, TypeAdapterFactory, JsonSerializer, or JsonDeserializer.
 *
 * @author Nate Brunette <n@tebru.net>
 *
 * @Annotation
 * @Target({"CLASS", "PROPERTY", "METHOD"})
 */
class JsonAdapter extends AbstractAnnotation
{
}
