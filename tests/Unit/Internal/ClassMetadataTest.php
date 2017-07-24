<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal;

use PHPUnit_Framework_TestCase;
use Tebru\AnnotationReader\AnnotationCollection;
use Tebru\Gson\Internal\DefaultClassMetadata;
use Tebru\Gson\Test\Mock\Annotation\FooAnnotation;
use Tebru\Gson\Test\Mock\Foo;

/**
 * Class ClassMetadataTest
 *
 * @author Nate Brunette <n@tebru.net>
 */
class ClassMetadataTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var AnnotationCollection
     */
    private $annotations;

    /**
     * @var DefaultClassMetadata
     */
    private $metadata;

    public function setUp()
    {
        $this->annotations = new AnnotationCollection();
        $this->metadata = new DefaultClassMetadata(Foo::class, $this->annotations);
    }

    public function testClassMetadata()
    {
        self::assertSame(Foo::class, $this->metadata->getName());
        self::assertSame($this->annotations, $this->metadata->getAnnotations());
    }

    public function testGetAnnotation()
    {
        $annotation = new FooAnnotation([]);
        $this->annotations->add($annotation);

        self::assertSame($annotation, $this->metadata->getAnnotation(FooAnnotation::class));
    }

    public function testGetAnnotationNull()
    {
        self::assertNull($this->metadata->getAnnotation(FooAnnotation::class));
    }
}
