<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Annotation;

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
    private $getter;

    /**
     * Method name representing setter
     *
     * @var string
     */
    private $setter;

    /**
     * Constructor
     *
     * @param string[] $params
     */
    public function __construct(array $params)
    {
        if (array_key_exists('get', $params)) {
            $this->getter = $params['get'];
        }

        if (array_key_exists('set', $params)) {
            $this->setter = $params['set'];
        }
    }

    /**
     * A method name representing the getter
     *
     * @return string
     */
    public function getter(): string
    {
        return $this->getter;
    }

    /**
     * A method name representing the setter
     *
     * @return string
     */
    public function setter(): string
    {
        return $this->setter;
    }
}
