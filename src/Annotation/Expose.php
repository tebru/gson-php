<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Annotation;

use Tebru\AnnotationReader\AbstractAnnotation;

/**
 * Class Expose
 *
 * Use this annotation to include serialization or deserialization of a property.  This
 * annotation only works with the flag to require this Expose annotation on the [@see Excluder].
 *
 * @author Nate Brunette <n@tebru.net>
 *
 * @Annotation
 * @Target({"CLASS", "PROPERTY", "METHOD"})
 */
class Expose extends AbstractAnnotation
{
    /**
     * Expose this property during serialization
     *
     * @var bool
     */
    private $serialize = true;

    /**
     * Expose this property during deserialization
     *
     * @var bool
     */
    private $deserialize = true;

    /**
     * Initialize annotation data
     */
    protected function init(): void
    {
        $this->serialize = $this->data['serialize'] ?? true;
        $this->deserialize = $this->data['deserialize'] ?? true;
    }

    /**
     * Returns true if the property should be exposed based on the direction (serialize/deserialize)
     *
     * @param bool $serialize
     * @return bool
     */
    public function shouldExpose(bool $serialize): bool
    {
        return $serialize ? $this->serialize : $this->deserialize;
    }
}
