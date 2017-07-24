<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal;

use Tebru\AnnotationReader\AnnotationCollection;
use Tebru\AnnotationReader\AnnotationReaderAdapter;
use Tebru\Gson\ClassMetadata;
use Tebru\Gson\Internal\Data\Property;
use Tebru\Gson\PropertyMetadata;

/**
 * Class MetadataFactory
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class MetadataFactory
{
    /**
     * Reads annotations from a class, property, or method and returns
     * an [@see AnnotationCollection]
     *
     * @var AnnotationReaderAdapter
     */
    private $annotationReader;

    /**
     * Constructor
     *
     * @param AnnotationReaderAdapter $annotationReader
     */
    public function __construct(AnnotationReaderAdapter $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    /**
     * Create class metadata
     *
     * @param string $className
     * @return ClassMetadata
     */
    public function createClassMetadata(string $className): ClassMetadata
    {
        return new DefaultClassMetadata($className, $this->annotationReader->readClass($className, true));
    }

    /**
     * Creates property metadata
     *
     * @param Property $property
     * @param ClassMetadata $classMetadata
     * @return PropertyMetadata
     */
    public function createPropertyMetadata(Property $property, ClassMetadata $classMetadata): PropertyMetadata
    {
        return new DefaultPropertyMetadata(
            $property->getRealName(),
            $property->getSerializedName(),
            $property->getType(),
            $property->getModifiers(),
            $classMetadata,
            $property->getAnnotations(),
            $property->isVirtual()
        );
    }
}
