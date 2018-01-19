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
    public const BEGIN_ARRAY = 'begin-array';
    public const END_ARRAY = 'end-array';
    public const BEGIN_OBJECT = 'begin-object';
    public const END_OBJECT = 'end-object';
    public const END_DOCUMENT = 'end-document';
    public const STRING = 'string';
    public const BOOLEAN = 'boolean';
    public const NUMBER = 'number';
    public const NULL = 'null';
    public const NAME = 'name';
}
