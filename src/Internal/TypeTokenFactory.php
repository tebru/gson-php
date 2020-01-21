<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal;

use InvalidArgumentException;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\ContextFactory;
use phpDocumentor\Reflection\Types\Null_;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;
use Reflector;
use Tebru\AnnotationReader\AnnotationCollection;
use Tebru\Gson\Annotation\Type;
use Tebru\PhpType\TypeToken;

/**
 * Class TypeToken
 *
 * Creates a [@see TypeToken] for a property
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class TypeTokenFactory
{
    /**
     * @var DocBlockFactory
     */
    private $docBlockFactory;

    /**
     * @var ContextFactory
     */
    private $contextFactory;

    /**
     * Constructor
     *
     * @param null|DocBlockFactory $docBlockFactory
     * @param null|ContextFactory $contextFactory
     */
    public function __construct(?DocBlockFactory $docBlockFactory = null, ?ContextFactory $contextFactory = null)
    {
        $this->docBlockFactory = $docBlockFactory ?? DocBlockFactory::createInstance();
        $this->contextFactory = $contextFactory ?? new ContextFactory();
    }

    /**
     * Attempts to guess a property type based method type hints, defaults to wildcard type
     * - @Type annotation if it exists
     * - Getter return type if it exists
     * - Setter typehint if it exists
     * - Getter docblock
     * - Setter docblock
     * - Property docblock
     * - Property default value if it exists
     * - Setter default value if it exists
     * - Defaults to wildcard type
     * @param AnnotationCollection $annotations
     * @param ReflectionProperty|null $property
     * @param ReflectionMethod|null $getterMethod
     * @param ReflectionMethod|null $setterMethod
     * @return TypeToken
     * @throws ReflectionException
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

        if (null !== $getterMethod && null !== $getterMethod->getReturnType()) {
            $getterType = TypeToken::create($getterMethod->getReturnType()->getName());
            return $this->checkGenericArray($getterType, $property, $getterMethod, $setterMethod);
        }

        if (null !== $setterMethod && [] !== $setterMethod->getParameters()) {
            $parameter = $setterMethod->getParameters()[0];
            if (null !== $parameter->getType()) {
                $setterType = TypeToken::create($parameter->getType()->getName());
                return $this->checkGenericArray($setterType, $property, $getterMethod, $setterMethod);
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

        if ($property !== null && $property->isDefault()) {
            $defaultProperty = $property->getDeclaringClass()->getDefaultProperties()[$property->getName()];
            if ($defaultProperty !== null) {
                return $this->checkGenericArray(
                    TypeToken::createFromVariable($defaultProperty),
                    $property,
                    $getterMethod,
                    $setterMethod
                );
            }
        }

        if (null !== $setterMethod && [] !== $setterMethod->getParameters()) {
            $parameter = $setterMethod->getParameters()[0];
            if ($parameter->isDefaultValueAvailable() && null !== $parameter->getDefaultValue()) {
                $setterType = TypeToken::create(gettype($parameter->getDefaultValue()));
                return $this->checkGenericArray($setterType, $property, $getterMethod, $setterMethod);
            }
        }

        return TypeToken::create(TypeToken::WILDCARD);
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
        $returnTag = null;

        if ($getter !== null) {
            $docComment = $getter->getDocComment() ?: null;
            $tag = $this->getTypeFromDoc($getter, $docComment, 'return');
            if ($tag !== null) {
                $returnTag = $tag;
                if ((string)$tag !== 'array') {
                    return $tag;
                }
            }
        }

        if ($setter !== null) {
            $docComment = $setter->getDocComment() ?: null;
            $parameters = $setter->getParameters();
            if (count($parameters) === 1) {
                $tag = $this->getTypeFromDoc($setter, $docComment, 'param', $parameters[0]->getName());
                if ($tag !== null) {
                    $returnTag = $tag;
                    if ((string)$tag !== 'array') {
                        return $tag;
                    }
                }
            }
        }

        if ($property !== null) {
            $docComment = $property->getDocComment() ?: null;
            $tag = $this->getTypeFromDoc($property, $docComment, 'var');
            if ($tag !== null) {
                $returnTag = $tag;
                if ((string)$tag !== 'array') {
                    return $tag;
                }
            }
        }

        return $returnTag;
    }

    /**
     * Get [@see TypeToken] from docblock
     *
     * @param Reflector $reflector
     * @param null|string $docComment
     * @param string $tagName
     * @param null|string $variableName
     * @return null|TypeToken
     */
    private function getTypeFromDoc(
        Reflector $reflector,
        ?string $docComment,
        string $tagName,
        ?string $variableName = null
    ): ?TypeToken {
        if ($docComment === null || $docComment === '') {
            return null;
        }

        try {
            $docblock = $this->docBlockFactory->create(
                $docComment,
                $this->contextFactory->createFromReflector($reflector)
            );
        } /** @noinspection BadExceptionsProcessingInspection */ catch (InvalidArgumentException $exception) {
            // exception likely caused by an empty type
            return null;
        }

        $tags = $docblock->getTagsByName($tagName);

        if (empty($tags)) {
            return null;
        }

        if ($tagName !== 'param') {
            return $this->getTypeFromTag($tags[0]);
        }

        /** @var Param $tag */
        foreach ($tags as $tag) {
            if ($tag->getVariableName() === $variableName) {
                return $this->getTypeFromTag($tag);
            }
        }

        return null;
    }

    /**
     * Get the type token from tag
     *
     * @param Var_|Param|Return_|Tag $tag
     * @return null|TypeToken
     */
    private function getTypeFromTag(Tag $tag): ?TypeToken
    {
        $type = $tag->getType();
        if (!$type instanceof Compound) {
            $type = $this->stripSlashes((string)$type);

            return TypeToken::create($this->unwrapArray($type));
        }

        $types = iterator_to_array($type->getIterator());
        $types = array_values(array_filter($types, static function ($innerType) {
            return !$innerType instanceof Null_;
        }));
        $count = count($types);

        if ($count !== 1) {
            return null;
        }

        $type = $this->stripSlashes((string)$types[0]);

        return TypeToken::create($this->unwrapArray($type));
    }

    /**
     * Remove the initial '\' if it exists
     *
     * @param string $type
     * @return string
     */
    private function stripSlashes(string $type): string
    {
        if ($type[0] === '\\') {
            $type = substr($type, 1);
        }

        return $type;
    }

    /**
     * Converts types as int[] to array<int>
     *
     * @param string $type
     * @return string
     */
    private function unwrapArray(string $type): string
    {
        // if not in array syntax
        if (strpos($type, '[]') === false) {
            // convert mixed to wildcard
            return $type === 'mixed' ? TypeToken::WILDCARD : $type;
        }

        $parts = explode('[]', $type);
        $primaryType = array_shift($parts);

        $numParts = count($parts);

        // same as mixed
        if ($primaryType === 'array') {
            $primaryType = TypeToken::WILDCARD;
            $numParts++;
        }

        return str_repeat('array<', $numParts) . $primaryType . str_repeat('>', $numParts);
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
        return $type->phpType === TypeToken::HASH
            ? $this->checkDocBlocks($property, $getter, $setter) ?? $type
            : $type;
    }
}
