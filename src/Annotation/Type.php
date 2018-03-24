<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Annotation;

use Tebru\AnnotationReader\AbstractAnnotation;
use Tebru\PhpType\TypeToken;

/**
 * Class Type
 *
 * Used to define an explicit type for a property.  Generally a type will tried to
 * be inferred using the value, type, type hints, return types, or default values; otherwise,
 * the type will be assumed to be primitive.  This annotation lets you explicitly define
 * a type or create a custom type that can be used by a TypeAdapter.
 *
 * @author Nate Brunette <n@tebru.net>
 *
 * @Annotation
 * @Target({"PROPERTY", "METHOD"})
 */
class Type extends AbstractAnnotation
{
    /**
     * Returns the php type
     *
     * @return TypeToken
     */
    public function getType(): TypeToken
    {
        return new TypeToken($this->getValue());
    }
}
