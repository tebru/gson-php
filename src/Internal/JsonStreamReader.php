<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Gson\Internal;

use Psr\Http\Message\StreamInterface;
use Tebru\Gson\Exception\MalformedJsonException;
use Tebru\Gson\Exception\UnexpectedJsonScopeException;
use Tebru\Gson\Exception\UnexpectedJsonTokenException;
use Tebru\Gson\Exception\UnexpectedJsonTypeException;
use Tebru\Gson\JsonReadable;
use Tebru\Gson\JsonToken;
use Tebru\Realtype\Realtype;

/**
 * Class JsonStreamReader
 *
 * Reads from a stream of json and provides an api to interact.
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class JsonStreamReader implements JsonReadable
{
    /**
     * A stream containing valid json to read
     *
     * @var StreamInterface
     */
    private $stream;

    /**
     * Manages [@see JsonScope] depth.  This allows the reader to know what
     * scope to use once a nested object has finished being parsed.
     *
     * @var JsonScope[]
     */
    private $stack;

    /**
     * A cache of the current [@see JsonToken].  This should get nulled out whenever
     * subsequent calls to [@see JsonStreamReader::peek] needs to return the next token.
     *
     * @var JsonToken
     */
    private $currentToken;

    /**
     * Constructor
     *
     * @param StreamInterface $stream
     */
    public function __construct(StreamInterface $stream)
    {
        $this->stream = $stream;
        $this->stack[] = JsonScope::EMPTY_DOCUMENT;
    }

    /**
     * Consumes the next token and asserts it's the beginning of a new array
     *
     * @return void
     * @throws \Tebru\Gson\Exception\MalformedJsonException If an unexpected character is encountered
     * @throws \RuntimeException If there's an error reading the stream
     * @throws \Tebru\Gson\Exception\UnexpectedJsonTokenException If next token does not match expectation
     */
    public function beginArray(): void
    {
        if ($this->peek() !== JsonToken::BEGIN_ARRAY) {
            $currentCharacter = $this->stream->read(1);
            throw new UnexpectedJsonTokenException(sprintf('Expected "[", but found "%s"', $currentCharacter));
        }

        $this->currentToken = null;
        $this->stream->read(1);
        $this->stack[] = JsonScope::EMPTY_ARRAY;
    }

    /**
     * Consumes the next token and asserts it's the end of an array
     *
     * @return void
     * @throws \Tebru\Gson\Exception\MalformedJsonException If an unexpected character is encountered
     * @throws \RuntimeException If there's an error reading the stream
     * @throws \Tebru\Gson\Exception\UnexpectedJsonTokenException If the next token does not match expectation
     */
    public function endArray(): void
    {
        if ($this->peek() !== JsonToken::END_ARRAY) {
            $currentCharacter = $this->stream->read(1);
            throw new UnexpectedJsonTokenException(sprintf('Expected "]", but found "%s"', $currentCharacter));
        }

        $this->currentToken = null;
        $this->stream->read(1);
        array_pop($this->stack);
    }

    /**
     * Consumes the next token and asserts it's the beginning of a new object
     *
     * @return void
     * @throws \Tebru\Gson\Exception\MalformedJsonException If an unexpected character is encountered
     * @throws \RuntimeException If there's an error reading the stream
     * @throws \Tebru\Gson\Exception\UnexpectedJsonTokenException If the next token does not match expectation
     */
    public function beginObject(): void
    {
        if ($this->peek() !== JsonToken::BEGIN_OBJECT) {
            $currentCharacter = $this->stream->read(1);
            throw new UnexpectedJsonTokenException(sprintf('Expected "{", but found "%s"', $currentCharacter));
        }

        $this->currentToken = null;
        $this->stream->read(1);
        $this->stack[] = JsonScope::EMPTY_OBJECT;
    }

    /**
     * Consumes the next token and asserts it's the end of an object
     *
     * @return void
     * @throws \Tebru\Gson\Exception\MalformedJsonException If an unexpected character is encountered
     * @throws \RuntimeException If there's an error reading the stream
     * @throws \Tebru\Gson\Exception\UnexpectedJsonTokenException If the next token does not match expectation
     */
    public function endObject(): void
    {
        if ($this->peek() !== JsonToken::END_OBJECT) {
            $currentCharacter = $this->stream->read(1);
            throw new UnexpectedJsonTokenException(sprintf('Expected "}", but found "%s"', $currentCharacter));
        }

        $this->currentToken = null;
        $this->stream->read(1);
        array_pop($this->stack);
    }

    /**
     * Returns true if the array or object has another element
     *
     * If the current scope is not an array or object, this returns false
     *
     * @return bool
     * @throws \Tebru\Gson\Exception\MalformedJsonException If an unexpected character is encountered
     * @throws \RuntimeException If there's an error reading the stream
     */
    public function hasNext(): bool
    {
        $nextChar = $this->nextNonWhitespace();

        return '}' !== $nextChar && ']' !== $nextChar;
    }

    /**
     * Consumes the value of the next token, asserts it's a boolean and returns it
     *
     * @return bool
     * @throws \Tebru\Gson\Exception\MalformedJsonException If an unexpected character is encountered
     * @throws \RuntimeException If there's an error reading the stream
     * @throws \Tebru\Gson\Exception\UnexpectedJsonTypeException If the scalar type is not a boolean
     * @throws \Tebru\Gson\Exception\UnexpectedJsonTokenException If the next token does not match expectation
     */
    public function nextBoolean(): bool
    {
        if ($this->peek() !== JsonToken::BOOLEAN) {
            throw new UnexpectedJsonTokenException(sprintf('Expected boolean, but got "%s"', $this->peek()));
        }

        $value = $this->nextScalar();

        if (!is_bool($value)) {
            throw new UnexpectedJsonTypeException(sprintf('Expected boolean, but got "%s"', gettype($value)));
        }

        return $value;
    }

    /**
     * Consumes the value of the next token, asserts it's a double and returns it
     *
     * @return double
     * @throws \Tebru\Gson\Exception\MalformedJsonException If an unexpected character is encountered
     * @throws \RuntimeException If there's an error reading the stream
     * @throws \Tebru\Gson\Exception\UnexpectedJsonTypeException If the scalar type is not a double
     * @throws \Tebru\Gson\Exception\UnexpectedJsonTokenException If the next token does not match expectation
     */
    public function nextDouble(): float
    {
        if ($this->peek() !== JsonToken::NUMBER) {
            throw new UnexpectedJsonTokenException(sprintf('Expected double, but got "%s"', $this->peek()));
        }

        $value = $this->nextScalar();

        if (!is_float($value) && !is_int($value)) {
            throw new UnexpectedJsonTypeException(sprintf('Expected double, but got "%s"', gettype($value)));
        }

        return (float) $value;
    }

    /**
     * Consumes the value of the next token, asserts it's an int and returns it
     *
     * @return int
     * @throws \Tebru\Gson\Exception\MalformedJsonException If an unexpected character is encountered
     * @throws \RuntimeException If there's an error reading the stream
     * @throws \Tebru\Gson\Exception\UnexpectedJsonTypeException If the scalar type is not an integer
     * @throws \Tebru\Gson\Exception\UnexpectedJsonTokenException If the next token does not match expectation
     */
    public function nextInteger(): int
    {
        if ($this->peek() !== JsonToken::NUMBER) {
            throw new UnexpectedJsonTokenException(sprintf('Expected integer, but got "%s"', $this->peek()));
        }

        $value = $this->nextScalar();

        if (!is_int($value)) {
            throw new UnexpectedJsonTypeException(sprintf('Expected integer, but got "%s"', gettype($value)));
        }

        return $value;
    }

    /**
     * Consumes the value of the next token, asserts it's a string and returns it
     *
     * @return string
     * @throws \Tebru\Gson\Exception\MalformedJsonException If an unexpected character is encountered
     * @throws \RuntimeException If there's an error reading the stream
     * @throws \Tebru\Gson\Exception\UnexpectedJsonTokenException If the next token does not match expectation
     */
    public function nextString(): string
    {
        $peek = $this->peek();
        if ($peek !== JsonToken::STRING && $peek !== JsonToken::NAME) {
            throw new UnexpectedJsonTokenException(sprintf('Expected string, but got "%s"', $peek));
        }

        // read past double quote
        $this->stream->read(1);

        // write to buffer because we want the literal value
        $buffer = [];
        $this->readUntil(['"'], $buffer);

        $this->currentToken = null;

        return implode($buffer);
    }

    /**
     * Consumes the value of the next token and asserts it's null
     *
     * @return void|null
     * @throws \Tebru\Gson\Exception\MalformedJsonException If an unexpected character is encountered
     * @throws \RuntimeException If there's an error reading the stream
     * @throws \Tebru\Gson\Exception\UnexpectedJsonTypeException If the scalar type is not null
     * @throws \Tebru\Gson\Exception\UnexpectedJsonTokenException If the next token does not match expectation
     */
    public function nextNull()
    {
        if ($this->peek() !== JsonToken::NULL) {
            throw new UnexpectedJsonTokenException(sprintf('Expected null, but got "%s"', $this->peek()));
        }

        $value = $this->nextScalar();

        if (null !== $value) {
            throw new UnexpectedJsonTypeException(sprintf('Expected null, but got "%s"', gettype($value)));
        }

        return null;
    }

    /**
     * Consumes the next name and returns it
     *
     * @return string
     * @throws \Tebru\Gson\Exception\UnexpectedJsonScopeException If the current scope is not allowed
     * @throws \RuntimeException If there's an error reading the stream
     * @throws \Tebru\Gson\Exception\MalformedJsonException When a double quote is missing
     * @throws \Tebru\Gson\Exception\UnexpectedJsonTokenException If the next token does not match expectation
     */
    public function nextName(): string
    {
        $scope = $this->currentScope();
        if ($scope !== JsonScope::EMPTY_OBJECT && $scope !== JsonScope::NONEMPTY_OBJECT) {
            throw new UnexpectedJsonScopeException(sprintf('Method call not allowed in current scope'));
        }

        return $this->nextString();
    }

    /**
     * Returns an enum representing the type of the next token without consuming it
     *
     * @return string
     * @throws \RuntimeException If there's an error reading the stream
     * @throws \Tebru\Gson\Exception\MalformedJsonException If an unexpected character is encountered
     */
    public function peek(): string
    {
        if (null !== $this->currentToken) {
            return $this->currentToken;
        }

        $stackEnd = count($this->stack) - 1;

        /** @var JsonScope $currentScope */
        $currentScope = $this->stack[$stackEnd];

        switch ($currentScope) {
            case JsonScope::EMPTY_ARRAY:
                // if the scope is currently an empty array, make it a non-empty array
                $this->stack[$stackEnd] = JsonScope::NONEMPTY_ARRAY;

                if (']' !== $this->nextNonWhitespace()) {
                    break;
                }

                // it actually is an empty array
                $this->stack[$stackEnd] = JsonScope::EMPTY_ARRAY;

                // if we're at the end of an array, return the token
                $this->currentToken = JsonToken::END_ARRAY;

                return $this->currentToken;
            case JsonScope::NONEMPTY_ARRAY:
                // if the scope is currently a non-empty array, the next character
                // must end the array or be a comma
                switch ($this->nextNonWhitespace()) {
                    case ']':
                        // if we're at the end of an array, return the token
                        $this->currentToken = JsonToken::END_ARRAY;

                        return $this->currentToken;
                    case ',':
                        // there are additional elements
                        // read past comma
                        $this->stream->read(1);
                        break;
                    default:
                        throw new MalformedJsonException(sprintf(
                            'Expected "]" or ",", but found "%s"',
                            $this->peekNextCharacter()
                        ));
                }
                break;
            case JsonScope::EMPTY_OBJECT:
            case JsonScope::NONEMPTY_OBJECT:
                // if the current scope is an empty/non-empty object, change it to be a dangling name
                $this->stack[$stackEnd] = JsonScope::DANGLING_NAME;

                // if the current scope is a non-empty object, the next character must end the object
                //or be a comma
                if ($currentScope === JsonScope::NONEMPTY_OBJECT) {
                    switch ($this->nextNonWhitespace()) {
                        case '}':
                            // if the current scope is an empty/non-empty object, change it to be a dangling name
                            $this->stack[$stackEnd] = JsonScope::NONEMPTY_OBJECT;

                            // if we're at the end of the object, return the token
                            $this->currentToken = JsonToken::END_OBJECT;

                            return $this->currentToken;
                        case ',':
                            // there are additional elements
                            // read past comma
                            $this->stream->read(1);

                            if ('"' !== $this->nextNonWhitespace()) {
                                throw new MalformedJsonException(sprintf('Expected ", but found "%s"',
                                    $this->peekNextCharacter()));
                            }

                            return JsonToken::NAME;
                        default:
                            throw new MalformedJsonException(sprintf('Expected "}" or ",", but found "%s"',
                                $this->peekNextCharacter()));
                    }
                }

                // if there's another element, it starts with a double quote, or we're at
                // the end of the object
                switch ($this->nextNonWhitespace()) {
                    case '}':
                        // if we're at the end of the object, return the token
                        $this->currentToken = JsonToken::END_OBJECT;

                        return $this->currentToken;
                    case '"':
                        // we're starting a new name
                        $this->currentToken = JsonToken::NAME;

                        return $this->currentToken;
                    default:
                        throw new MalformedJsonException(sprintf('Expected " or "}", but found "%s"',
                            $this->peekNextCharacter()));
                }
                break;
            case JsonScope::DANGLING_NAME:
                $this->stack[$stackEnd] = JsonScope::NONEMPTY_OBJECT;

                if (':' !== $this->nextNonWhitespace()) {
                    throw new MalformedJsonException(sprintf('Expected ":", but found "%s"',
                        $this->peekNextCharacter()));
                }

                // next character must be a colon
                // skip past colon
                $this->stream->read(1);
                break;
            case JsonScope::EMPTY_DOCUMENT:
                $this->stack[$stackEnd] = JsonScope::NONEMPTY_DOCUMENT;

                switch ($this->nextNonWhitespace()) {
                    case '[':
                        $this->currentToken = JsonToken::BEGIN_ARRAY;

                        return $this->currentToken;
                    case '{':
                        $this->currentToken = JsonToken::BEGIN_OBJECT;

                        return $this->currentToken;
                    default:
                        break;
                }
        }

        // if we're here, we're trying to determine a type of value
        switch ($this->nextNonWhitespace()) {
            case '[':
                $this->currentToken = JsonToken::BEGIN_ARRAY;

                return $this->currentToken;
            case '{':
                $this->currentToken = JsonToken::BEGIN_OBJECT;

                return $this->currentToken;
            case '"':
                $this->currentToken = JsonToken::STRING;

                return $this->currentToken;
            case 't':
            case 'T':
                if ('true' === strtolower($this->peekNextCharacter(4))) {
                    $this->currentToken = JsonToken::BOOLEAN;

                    return $this->currentToken;
                }

                throw new MalformedJsonException(sprintf('Expected "true", but found "%s"',
                    $this->peekNextCharacter(4)));
            case 'f':
            case 'F':
                if ('false' === strtolower($this->peekNextCharacter(5))) {
                    $this->currentToken = JsonToken::BOOLEAN;

                    return $this->currentToken;
                }

                throw new MalformedJsonException(sprintf('Expected "false", but found "%s"',
                    $this->peekNextCharacter(5)));
            case 'n':
            case 'N':
                if ('null' === strtolower($this->peekNextCharacter(4))) {
                    $this->currentToken = JsonToken::NULL;

                    return $this->currentToken;
                }

                throw new MalformedJsonException(sprintf('Expected "null", but found "%s"',
                    $this->peekNextCharacter(4)));
            case '0':
            case '1':
            case '2':
            case '3':
            case '4':
            case '5':
            case '6':
            case '7':
            case '8':
            case '9':
            case '-':
                $this->currentToken = JsonToken::NUMBER;

                return $this->currentToken;
            default:
                throw new MalformedJsonException(sprintf('Unable to handle "%s" character',
                    $this->peekNextCharacter()));
        }
    }

    /**
     * Skip the next value.  If the next value is an object or array, all children will
     * also be skipped.
     *
     * @return void
     * @throws \RuntimeException If there's an error reading the stream
     */
    public function skipValue(): void
    {
        $depth = 0;
        do {
            switch ($this->peek()) {
                case JsonToken::BEGIN_ARRAY:
                    $depth++;
                    $this->beginArray();
                    break;
                case JsonToken::END_ARRAY:
                    $depth--;
                    $this->endArray();
                    break;
                case JsonToken::BEGIN_OBJECT:
                    $depth++;
                    $this->beginObject();
                    break;
                case JsonToken::END_OBJECT:
                    $depth--;
                    $this->endObject();
                    break;
                case JsonToken::NAME:
                case JsonToken::STRING:
                    $this->nextString();
                    break;
                default:
                    $this->nextScalar();
            }
        } while ($depth !== 0);
    }

    /**
     * Peek at the next character in the stream and reset
     *
     * @param int $bytes
     * @return string
     * @throws \RuntimeException If there's an error reading the stream
     */
    private function peekNextCharacter(int $bytes = 1): string
    {
        $character = $this->stream->read($bytes);

        $currentPosition = $this->stream->tell();
        if ($bytes > $currentPosition) {
            $this->stream->rewind();
        } else {
            $this->back($bytes);
        }

        return $character;
    }

    /**
     * Peek at the next non-whitespace character in the stream and move back one
     *
     * @return string
     * @throws \RuntimeException If there's an error reading the stream
     */
    private function nextNonWhitespace(): string
    {
        do {
            $character = $this->stream->read(1);
        } while (ctype_space($character));

        $this->back();

        return $character;
    }

    /**
     * Read the next scalar json value, then move back one
     *
     * Optionally pass in an array by reference to get the list
     * of characters read
     *
     * @param array $buffer
     * @return bool|float|int|string
     * @throws \RuntimeException If there's an error reading the stream
     */
    private function nextScalar(array &$buffer = [])
    {
        $this->readUntil([',', ']', '}', '"'], $buffer);

        $this->currentToken = null;
        $this->back();

        return Realtype::get(implode($buffer));
    }

    /**
     * Given an array of characters, read the stream until one of the
     * characters is found.
     *
     * Optionally pass in an array by reference to get the list
     * of characters read
     *
     * @param array $characters
     * @param array $buffer
     * @return string
     * @throws \RuntimeException If there's an error reading the stream
     */
    private function readUntil(array $characters, array &$buffer = []): string
    {
        do {
            $character = $this->stream->read(1);

            if (ctype_space($character)) {
                continue;
            }

            $buffer[] = $character;

            if ('' === $character) {
                return '';
            }

            // skip literals
            if ('\\' === $character) {
                $buffer[] = $this->stream->read(1);
            }
        } while (!in_array($character, $characters, true));

        // remove the last read character
        array_pop($buffer);

        // return the character found
        return $character;
    }

    /**
     * Move back in the stream x bytes
     *
     * @param int $bytes
     * @return void
     * @throws \RuntimeException If there's an error reading the stream
     */
    private function back(int $bytes = 1)
    {
        $this->stream->seek(-$bytes, SEEK_CUR);
    }

    /**
     * Get the current [@see JsonScope]
     *
     * @return string
     */
    private function currentScope(): string
    {
        return $this->stack[count($this->stack) - 1];
    }
}
