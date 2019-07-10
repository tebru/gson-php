<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace PHPSTORM_META;

use Tebru\AnnotationReader\AnnotationCollection;
use Tebru\Gson\ClassMetadata;
use Tebru\Gson\Gson;
use Tebru\Gson\PropertyMetadata;

override(Gson::fromJson(1), map(['' => '@']));
override(Gson::fromArray(1), map(['' => '@']));
override(AnnotationCollection::get(0), map(['' => '@']));
override(ClassMetadata::getAnnotation(0), map(['' => '@']));
override(PropertyMetadata::getAnnotation(0), map(['' => '@']));
