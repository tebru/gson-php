<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Annotation;

use OutOfBoundsException;

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
class Accessor
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
     * @param string[] $params
     * @throws \OutOfBoundsException
     */
    public function __construct(array $params)
    {
        if (isset($params['get'])) {
            $this->get = $params['get'];
        }

        if (isset($params['set'])) {
            $this->set = $params['set'];
        }

        if (null === $this->get && null === $this->set) {
            throw new OutOfBoundsException('@Accessor annotation must specify either get or set key');
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
