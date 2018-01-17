<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Annotation;

use Tebru\AnnotationReader\AbstractAnnotation;

/**
 * Class VirtualProperty
 *
 * This allows a method to be used as a property during serialization only. This
 * is helpful if your serialized models need to contain extra properties.  For example,
 * an aggregate of two separate properties on the model.
 *
 * If used on a class it will add a wrapped property for serialization or deserialization.
 *
 * For example, if you had this json
 *
 * {"data": {"id": 1}}
 *
 * You could deserialize into a class with an id property that has @VirtualProperty("data") on the
 * class. Similarly, serializing that object would produce the above json.
 *
 * The value of this annotation acts as the serialized name. If a SerializedName annotation also exists, that will
 * take precedence. Please note that this will always wrap classes of the specified type, regardless of where they
 * occur in the tree.
 *
 * @author Nate Brunette <n@tebru.net>
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class VirtualProperty extends AbstractAnnotation
{
    /**
     * Initialize annotation data
     */
    protected function init(): void
    {
        $this->value = $this->data['value'] ?? null;
    }

    /**
     * Return the serialized name if it exists or null
     *
     * @return null|string
     */
    public function getSerializedName(): ?string
    {
        return $this->value;
    }
}
