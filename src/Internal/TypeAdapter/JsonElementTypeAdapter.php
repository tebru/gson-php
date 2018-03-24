<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

declare(strict_types=1);

namespace Tebru\Gson\Internal\TypeAdapter;

use LogicException;
use Tebru\Gson\Element\JsonArray;
use Tebru\Gson\Element\JsonElement;
use Tebru\Gson\Element\JsonNull;
use Tebru\Gson\Element\JsonObject;
use Tebru\Gson\Element\JsonPrimitive;
use Tebru\Gson\JsonWritable;
use Tebru\Gson\JsonReadable;
use Tebru\Gson\JsonToken;
use Tebru\Gson\TypeAdapter;

/**
 * Class JsonElementTypeAdapter
 *
 * @author Nate Brunette <n@tebru.net>
 */
final class JsonElementTypeAdapter extends TypeAdapter
{
    /**
     * Read the next value, convert it to its type and return it
     *
     * @param JsonReadable $reader
     * @return JsonElement
     * @throws \LogicException
     */
    public function read(JsonReadable $reader): JsonElement
    {
        switch ($reader->peek()) {
            case JsonToken::BEGIN_OBJECT:
                $object = new JsonObject();
                $reader->beginObject();
                while ($reader->hasNext()) {
                    $name = $reader->nextName();
                    $object->add($name, $this->read($reader));
                }
                $reader->endObject();

                return $object;
            case JsonToken::BEGIN_ARRAY:
                $array = new JsonArray();
                $reader->beginArray();
                while ($reader->hasNext()) {
                    $array->addJsonElement($this->read($reader));
                }
                $reader->endArray();

                return $array;
            case JsonToken::STRING:
                return JsonPrimitive::create($reader->nextString());
            case JsonToken::NUMBER:
                return JsonPrimitive::create($reader->nextDouble());
            case JsonToken::BOOLEAN:
                return JsonPrimitive::create($reader->nextBoolean());
            case JsonToken::NULL:
                $reader->nextNull();

                return new JsonNull();
            default:
                throw new LogicException(\sprintf('Could not handle token "%s" at "%s"', $reader->peek(), $reader->getPath()));
        }
    }

    /**
     * Write the value to the writer for the type
     *
     * @param JsonWritable $writer
     * @param JsonElement $value
     * @return void
     */
    public function write(JsonWritable $writer, $value): void
    {
        if (null === $value || $value->isJsonNull()) {
            $writer->writeNull();

            return;
        }

        if ($value->isJsonObject()) {
            $writer->beginObject();
            foreach ($value->asJsonObject() as $key => $element) {
                $writer->name($key);
                $this->write($writer, $element);
            }
            $writer->endObject();

            return;
        }

        if ($value->isJsonArray()) {
            $writer->beginArray();
            foreach ($value->asJsonArray() as $element) {
                $this->write($writer, $element);
            }
            $writer->endArray();

            return;
        }

        if ($value->isInteger()) {
            $writer->writeInteger($value->asInteger());

            return;
        }

        if ($value->isFloat()) {
            $writer->writeFloat($value->asFloat());

            return;
        }

        if ($value->isBoolean()) {
            $writer->writeBoolean($value->asBoolean());

            return;
        }

        $writer->writeString($value->asString());
    }
}
