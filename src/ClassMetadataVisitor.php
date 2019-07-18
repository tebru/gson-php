<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson;

/**
 * Interface PropertyCollectionVisitor
 *
 * Will be called when [@see ClassMetadata] is first created. Use this to manipulate
 * the class/property metadata before getting passed to the type adapter.
 *
 * @author Nate Brunette <n@tebru.net>
 */
interface ClassMetadataVisitor
{
    /**
     * Handle the class or property metadata
     *
     * @param ClassMetadata $classMetadata
     */
    public function onLoaded(ClassMetadata $classMetadata): void;
}
