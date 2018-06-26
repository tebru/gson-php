<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal;

use ReflectionMethod;
use ReflectionProperty;
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
     * Regex to get full class names from imported use statements
     */
    private const USE_PATTERN = '/use\s+(?:(?<namespace>[^;]+\\\\)[^;]*[\s,{](?<classname>\w+)\s+as\s+:REPLACE:[^;]*};|(?<group>[^;]+\\\\){[^;]*:REPLACE:[^;]*};|(?<alias>[^;]+)\s+as\s+:REPLACE:;|(?<default>[^;]+:REPLACE:);)/';

    /**
     * Attempts to guess a property type based method type hints, defaults to wildcard type
     *
     * - Uses a @Type annotation if it exists
     * - Uses setter typehint if it exists
     * - Uses getter return type if it exists
     * - Uses setter default value if it exists
     * - Uses property docblock
     * - Uses getter docblock
     * - Uses setter docblock
     * - Defaults to wildcard type
     *
     * @param AnnotationCollection $annotations
     * @param ReflectionProperty|null $property
     * @param ReflectionMethod|null $getterMethod
     * @param ReflectionMethod|null $setterMethod
     * @return TypeToken
     */
    public function create(
        AnnotationCollection $annotations,
        ?ReflectionMethod $getterMethod = null,
        ?ReflectionMethod $setterMethod = null,
        ?ReflectionProperty $property = null
    ): TypeToken {
        /** @var Type $typeAnnotation */
        $typeAnnotation = $annotations->get(Type::class);

        if (null !== $typeAnnotation) {
            return $typeAnnotation->getType();
        }

        if (null !== $setterMethod && [] !== $setterMethod->getParameters()) {
            $parameter = $setterMethod->getParameters()[0];
            if (null !== $parameter->getType()) {
                return $this->checkGenericArray(
                    new TypeToken((string)$parameter->getType()),
                    $property,
                    $getterMethod,
                    $setterMethod
                );
            }
        }

        if (null !== $getterMethod && null !== $getterMethod->getReturnType()) {
            return $this->checkGenericArray(
                new TypeToken((string)$getterMethod->getReturnType()),
                $property,
                $getterMethod,
                $setterMethod
            );
        }

        if (null !== $setterMethod && [] !== $setterMethod->getParameters()) {
            $parameter = $setterMethod->getParameters()[0];
            if ($parameter->isDefaultValueAvailable() && null !== $parameter->getDefaultValue()) {
                return $this->checkGenericArray(
                    new TypeToken(\gettype($parameter->getDefaultValue())),
                    $property,
                    $getterMethod,
                    $setterMethod
                );
            }
        }

        $type = $this->checkDocBlocks($property, $getterMethod, $setterMethod);
        if ($type !== null) {
            return $this->checkGenericArray(
                $type,
                $property,
                $getterMethod,
                $setterMethod
            );
        }

        return new TypeToken(TypeToken::WILDCARD);
    }

    /**
     * Attempt to get type from docblocks
     *
     * Checking in the order of property, getter, setter:
     *   Attempt to pull the type from the relevant portion of the docblock, then
     *   convert that type to a [@see TypeToken] converting to the full class name or
     *   generic array syntax if relevant.
     *
     * @param null|ReflectionProperty $property
     * @param null|ReflectionMethod $getter
     * @param null|ReflectionMethod $setter
     * @return null|TypeToken
     */
    private function checkDocBlocks(
        ?ReflectionProperty $property,
        ?ReflectionMethod $getter,
        ?ReflectionMethod $setter
    ): ?TypeToken {
        if ($property !== null) {
            $type = $this->getType($property->getDocComment() ?: null, 'var');
            if ($type !== null) {
                $class = $property->getDeclaringClass();
                return $this->getTypeToken($type, $class->getNamespaceName(), $class->getFileName());
            }
        }

        if ($getter !== null) {
            $type = $this->getType($getter->getDocComment() ?: null, 'return');
            if ($type !== null) {
                $class = $getter->getDeclaringClass();
                return $this->getTypeToken($type, $class->getNamespaceName(), $class->getFileName());
            }
        }

        if ($setter !== null) {
            $parameters = $setter->getParameters();
            if (\count($parameters) === 1) {
                $type = $this->getType($setter->getDocComment() ?: null, 'param', $parameters[0]->getName());
                if ($type !== null) {
                    $class = $setter->getDeclaringClass();
                    return $this->getTypeToken($type, $class->getNamespaceName(), $class->getFileName());
                }
            }
        }

        return null;
    }

    /**
     * Parse docblock and return type for parameter
     *
     * @param string $comment
     * @param string $annotation
     * @param null|string $parameter
     * @return null|string
     */
    private function getType(?string $comment, string $annotation, ?string $parameter = null): ?string
    {
        if ($comment === null) {
            return null;
        }

        // for setters, we look for the param name as well
        $pattern = '/@'.$annotation.'\s+([a-zA-Z0-9|\[\]\\\\]+)';
        if ($parameter !== null) {
            $pattern .= '\s+\$'.$parameter;
        }
        $pattern .= '/';

        \preg_match($pattern, $comment, $matches);

        /** @var string $type */
        $type = $matches[1] ?? null;
        if ($type === null) {
            return null;
        }

        // if not nullable
        if (\strpos($type, '|') === false) {
            return $type;
        }

        // if > 2 types
        if (\substr_count($type, '|') !== 1) {
            return null;
        }

        // if one of the types is not null
        if (\stripos(\strtolower($type), 'null') === false) {
            return null;
        }

        // return the non-null type
        foreach(\explode('|', $type) as $potentialType) {
            $potentialType = \trim($potentialType);
            if (\strtolower($potentialType) !== 'null') {
                return $potentialType;
            }
        }

        // The should never be hit
        // @codeCoverageIgnoreStart
        return null;
        // @codeCoverageIgnoreEnd
    }

    /**
     * Converts types as int[] to array<int>
     *
     * @param string $type
     * @param string $namespace
     * @param string $filename
     * @return string
     */
    private function unwrapArray(string $type, string $namespace, string $filename): string
    {
        // if not in array syntax
        if (\strpos($type, '[]') === false) {
            // convert mixed to wildcard
            return $type === 'mixed' ? TypeToken::WILDCARD : $type;
        }

        $parts = \explode('[]', $type);
        $primaryType = \array_shift($parts);
        $numParts = \count($parts);

        $primaryTypeToken = $this->getTypeToken($primaryType, $namespace, $filename);

        return \str_repeat('array<', $numParts) . $primaryTypeToken->getRawType() . \str_repeat('>', $numParts);
    }

    /**
     * Using the type found in docblock, attempt to resolve imported classes
     *
     * @param string $type
     * @param string $namespace
     * @param string $filename
     * @return TypeToken
     */
    private function getTypeToken(string $type, string $namespace, string $filename): TypeToken
    {
        // convert syntax if generic array
        $type = $this->unwrapArray($type, $namespace, $filename);
        $typeToken = new TypeToken($type);

        if (!$typeToken->isObject()) {
            return $typeToken;
        }

        $firstSlash = strpos($type, '\\');
        if ($firstSlash === 0) {
            return new TypeToken(substr($type, 1));
        }

        if ($firstSlash === false && (class_exists($type) || interface_exists($type))) {
            return $typeToken;
        }

        $pattern = \str_replace(':REPLACE:', $type, self::USE_PATTERN);
        \preg_match($pattern, \file_get_contents($filename), $matches);

        // normal use statement syntax
        if (!empty($matches['default'])) {
            return new TypeToken($matches['default']);
        }

        // aliased use statement
        if (!empty($matches['alias'])) {
            return new TypeToken($matches['alias']);
        }

        // group use statement
        if (!empty($matches['group'])) {
            return new TypeToken($matches['group'].$type);
        }

        // grouped aliased use statement
        if (!empty($matches['namespace']) && !empty($matches['classname'])) {
            return new TypeToken($matches['namespace'].$matches['classname']);
        }

        return new TypeToken($namespace.'\\'.$type);
    }

    /**
     * If the type is just 'array', check the docblock to see if there's a more specific type
     *
     * @param TypeToken $type
     * @param null|ReflectionProperty $property
     * @param null|ReflectionMethod $getter
     * @param null|ReflectionMethod $setter
     * @return TypeToken
     */
    private function checkGenericArray(
        TypeToken $type,
        ?ReflectionProperty $property,
        ?ReflectionMethod $getter,
        ?ReflectionMethod $setter
    ): TypeToken {
        return $type->isArray()
            ? $this->checkDocBlocks($property, $getter, $setter) ?? $type
            : $type;
    }
}
