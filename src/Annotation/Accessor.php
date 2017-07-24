<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Annotation;

use RuntimeException;
use Tebru\AnnotationReader\AbstractAnnotation;

/**
 * Class Accessor
 *
 * Use this annotation to explicitly define a getter/setter method mapping
 *
 * @author Nate Brunette <n@tebru.net>
 *
 * @Annotation
 * @Target({"PROPERTY"})
 */
class Accessor extends AbstractAnnotation
{
    /**
     * Method name representing getter
     *
     * @var string
     */
    private $get;

    /**
     * Method name representing setter
     *
     * @var string
     */
    private $set;

    /**
     * Constructor
     *
     * @throws \RuntimeException
     */
    protected function init(): void
    {
        $this->get = $this->data['get'] ?? null;
        $this->set = $this->data['set'] ?? null;

        if (null === $this->get && null === $this->set) {
            throw new RuntimeException('@Accessor annotation must specify either get or set key');
        }
    }

    /**
     * A method name representing the getter
     *
     * @return string
     */
    public function getter(): ?string
    {
        return $this->get;
    }

    /**
     * A method name representing the setter
     *
     * @return string
     */
    public function setter(): ?string
    {
        return $this->set;
    }
}
