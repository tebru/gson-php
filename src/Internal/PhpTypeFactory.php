<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal;

use ReflectionMethod;
use Tebru\Collection\SetInterface;
use Tebru\Gson\Annotation\Type;

/**
 * Class PhpTypeFactory
 *
 * Creates a [@see PhpType] for a property
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class PhpTypeFactory
{
    /**
     * Attempts to guess a property type based method type hints, defaults to wildcard type
     *
     * - Uses a @Type annotation if it exists
     * - Uses setter typehint if it exists
     * - Uses getter return type if it exists
     * - Uses setter default value if it exists
     * - Defaults to wildcard type
     *
     * @param SetInterface $annotations
     * @param ReflectionMethod|null $getterMethod
     * @param ReflectionMethod|null $setterMethod
     * @return PhpType
     * @throws \RuntimeException If the value is not valid
     * @throws \Tebru\Gson\Exception\MalformedTypeException If the type cannot be parsed
     */
    public function create(SetInterface $annotations, ReflectionMethod $getterMethod = null, ReflectionMethod $setterMethod = null): PhpType
    {
        /** @var Type $typeAnnotation */
        $typeAnnotation = $annotations->find(function ($element) { return $element instanceof Type; });

        if (null !== $typeAnnotation) {
            return new PhpType($typeAnnotation->getType());
        }

        if (null !== $setterMethod && [] !== $setterMethod->getParameters()) {
            $parameter = $setterMethod->getParameters()[0];
            if (null !== $parameter->getType()) {
                return new PhpType((string) $parameter->getType());
            }
        }

        if (null !== $getterMethod && null !== $getterMethod->getReturnType()) {
            return new PhpType((string) $getterMethod->getReturnType());
        }

        if (null !== $setterMethod && [] !== $setterMethod->getParameters()) {
            $parameter = $setterMethod->getParameters()[0];
            if ($parameter->isDefaultValueAvailable() && null !== $parameter->getDefaultValue()) {
                return new PhpType(gettype($parameter->getDefaultValue()));
            }
        }

        return new PhpType(TypeToken::WILDCARD);
    }
}
