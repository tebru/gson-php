<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Test\Unit\Internal\Data;

use Doctrine\Common\Cache\ArrayCache;
use PHPUnit_Framework_TestCase;
use Tebru\Gson\Annotation\SerializedName;
use Tebru\Gson\Annotation\Since;
use Tebru\Gson\Annotation\Type;
use Tebru\Gson\Annotation\Until;
use Tebru\Gson\Annotation\VirtualProperty;
use Tebru\Gson\Internal\Data\AnnotationCollectionFactory;
use Tebru\Gson\Internal\Data\AnnotationSet;
use Tebru\Gson\Test\Mock\Unit\Internal\Data\AnnotationCollectionFactoryTest\AnnotationCollectionFactoryTestChildMock;
use Tebru\Gson\Test\Mock\Unit\Internal\Data\AnnotationCollectionFactoryTest\AnnotationCollectionFactoryTestParentMock;
use Tebru\Gson\Test\MockProvider;

/**
 * Class AnnotationCollectionFactoryTest
 *
 * @author Nate Brunette <n@tebru.net>
 * @covers \Tebru\Gson\Internal\Data\AnnotationCollectionFactory
 */
class AnnotationCollectionFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ArrayCache
     */
    private $cache;

    /**
     * @var AnnotationCollectionFactory
     */
    private $annotationCollectionFactory;

    public function setUp()
    {
        $this->cache = new ArrayCache();
        $this->annotationCollectionFactory = MockProvider::annotationCollectionFactory($this->cache);
    }

    public function testGetPropertyAnnotationsWithoutParent()
    {
        $annotations = $this->annotationCollectionFactory->createPropertyAnnotations(AnnotationCollectionFactoryTestParentMock::class, 'noParents');

        $expected = [
            new Type(['value' => 'int']),
            new SerializedName(['value' => 'no_parents']),
        ];

        self::assertEquals($expected, $annotations->toArray(AnnotationSet::TYPE_PROPERTY));
    }

    public function testGetPropertyAnnotationsWithParent()
    {
        $annotations = $this->annotationCollectionFactory->createPropertyAnnotations(AnnotationCollectionFactoryTestChildMock::class, 'withParent');

        $expected = [
            new Type(['value' => 'int']),
            new SerializedName(['value' => 'with_parents']),
            new Since(['value' => '1'])
        ];

        self::assertEquals($expected, $annotations->toArray(AnnotationSet::TYPE_PROPERTY));
    }

    public function testGetClassAnnotationsWithoutParent()
    {
        $annotations = $this->annotationCollectionFactory->createClassAnnotations(AnnotationCollectionFactoryTestParentMock::class);

        $expected = [
            new Since(['value' => '1']),
            new Until(['value' => '3']),
        ];

        self::assertEquals($expected, $annotations->toArray(AnnotationSet::TYPE_CLASS));
    }

    public function testGetClassAnnotationsWithParent()
    {
        $annotations = $this->annotationCollectionFactory->createClassAnnotations(AnnotationCollectionFactoryTestChildMock::class);

        $expected = [
            new Since(['value' => '2']),
            new Until(['value' => '3']),
        ];

        self::assertEquals($expected, $annotations->toArray(AnnotationSet::TYPE_CLASS));
    }

    public function testGetMethodAnnotationsWithoutParent()
    {
        $annotations = $this->annotationCollectionFactory->createMethodAnnotations(AnnotationCollectionFactoryTestChildMock::class, 'method1');

        $expected = [
            new VirtualProperty(),
            new SerializedName(['value' => 'method_1']),
        ];

        self::assertEquals($expected, $annotations->toArray(AnnotationSet::TYPE_METHOD));
    }

    public function testGetMethodAnnotationsWithParent()
    {
        $annotations = $this->annotationCollectionFactory->createMethodAnnotations(AnnotationCollectionFactoryTestChildMock::class, 'method2');

        $expected = [
            new VirtualProperty(),
            new SerializedName(['value' => 'method_2']),
            new Type(['value' => 'int']),
        ];

        self::assertEquals($expected, $annotations->toArray(AnnotationSet::TYPE_METHOD));
    }

    public function testCreatePropertyAnnotationsUsesCache()
    {
        $cachedAnnotations = new AnnotationSet();
        $cachedAnnotations->addAnnotation(new Type(['value' => 'string']), AnnotationSet::TYPE_PROPERTY);

        $this->cache->save('annotations:'.AnnotationCollectionFactoryTestParentMock::class.':noParents', $cachedAnnotations);
        $annotations = $this->annotationCollectionFactory->createPropertyAnnotations(AnnotationCollectionFactoryTestParentMock::class, 'noParents');

        self::assertSame($cachedAnnotations, $annotations);
    }

    public function testCreateClassAnnotationsUsesCache()
    {
        $cachedAnnotations = new AnnotationSet();
        $cachedAnnotations->addAnnotation(new Type(['value' => 'string']), AnnotationSet::TYPE_CLASS);

        $this->cache->save(AnnotationCollectionFactoryTestParentMock::class, $cachedAnnotations);
        $annotations = $this->annotationCollectionFactory->createClassAnnotations(AnnotationCollectionFactoryTestParentMock::class);

        self::assertSame($cachedAnnotations, $annotations);
    }

    public function testCreateMethodAnnotationsUsesCache()
    {
        $cachedAnnotations = new AnnotationSet();
        $cachedAnnotations->addAnnotation(new Type(['value' => 'string']), AnnotationSet::TYPE_METHOD);

        $this->cache->save(AnnotationCollectionFactoryTestParentMock::class.':'.'method1', $cachedAnnotations);
        $annotations = $this->annotationCollectionFactory->createMethodAnnotations(AnnotationCollectionFactoryTestParentMock::class, 'method1');

        self::assertSame($cachedAnnotations, $annotations);
    }
}
