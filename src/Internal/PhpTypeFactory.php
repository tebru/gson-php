<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal;

use ReflectionMethod;
use Tebru\AnnotationReader\AnnotationCollection;
use Tebru\Gson\Annotation\Type;
use Tebru\PhpType\TypeToken;

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
     * @param AnnotationCollection $annotations
     * @param ReflectionMethod|null $getterMethod
     * @param ReflectionMethod|null $setterMethod
     * @return TypeToken
     * @throws \Tebru\PhpType\Exception\MalformedTypeException If the type cannot be parsed
     */
    public function create(
        AnnotationCollection $annotations,
        ReflectionMethod $getterMethod = null,
        ReflectionMethod $setterMethod = null
    ): TypeToken {
        /** @var Type $typeAnnotation */
        $typeAnnotation = $annotations->get(Type::class);

        if (null !== $typeAnnotation) {
            return $typeAnnotation->getType();
        }

        if (null !== $setterMethod && [] !== $setterMethod->getParameters()) {
            $parameter = $setterMethod->getParameters()[0];
            if (null !== $parameter->getType()) {
                return new TypeToken((string) $parameter->getType());
            }
        }

        if (null !== $getterMethod && null !== $getterMethod->getReturnType()) {
            return new TypeToken((string) $getterMethod->getReturnType());
        }

        if (null !== $setterMethod && [] !== $setterMethod->getParameters()) {
            $parameter = $setterMethod->getParameters()[0];
            if ($parameter->isDefaultValueAvailable() && null !== $parameter->getDefaultValue()) {
                return new TypeToken(gettype($parameter->getDefaultValue()));
            }
        }

        return new TypeToken(TypeToken::WILDCARD);
    }
}
