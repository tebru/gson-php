<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Annotation;

use Tebru\AnnotationReader\AbstractAnnotation;

/**
 * Class Exclude
 *
 * Use this annotation to exclude serialization or deserialization of a property
 * that would otherwise be included.
 *
 * @author Nate Brunette <n@tebru.net>
 *
 * @Annotation
 * @Target({"CLASS", "PROPERTY", "METHOD"})
 */
class Exclude extends AbstractAnnotation
{
    /**
     * Exclude this property during serialization
     *
     * @var bool
     */
    private $serialize = true;

    /**
     * Exclude this property during deserialization
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
     * Returns true if the property should be excluded based on the direction (serialize/deserialize)
     *
     * @param bool $serialize
     * @return bool
     */
    public function shouldExclude(bool $serialize): bool
    {
        return $serialize ? $this->serialize : $this->deserialize;
    }
}
