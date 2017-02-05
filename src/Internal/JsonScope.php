<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal;

/**
 * Class JsonScope
 *
 * Represent the state during json parsing
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class JsonScope
{
    const EMPTY_ARRAY = 'empty-array';
    const NONEMPTY_ARRAY = 'nonempty-array';
    const EMPTY_OBJECT = 'empty-object';
    const DANGLING_NAME = 'dangling-name';
    const NONEMPTY_OBJECT = 'nonempty-object';
    const EMPTY_DOCUMENT = 'empty-document';
    const NONEMPTY_DOCUMENT = 'nonempty-document';
}
