<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson;

/**
 * Class JsonToken
 *
 * Represents json types
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class JsonToken
{
    const BEGIN_ARRAY = 'begin-array';
    const END_ARRAY = 'end-array';
    const BEGIN_OBJECT = 'begin-object';
    const END_OBJECT = 'end-object';
    const END_DOCUMENT = 'end-document';
    const STRING = 'string';
    const BOOLEAN = 'boolean';
    const NUMBER = 'number';
    const NULL = 'null';
    const NAME = 'name';
}
