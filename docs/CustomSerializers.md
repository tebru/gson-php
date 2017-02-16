Customizing Serialization/Deserialization
=========================================

There are multiple ways that serialization or deserialization can be
customized.

Custom Serializer
-----------------

By implementing the `JsonSerializer` interface, you can completely
control how serialization is accomplished.  You will receive the
object that should be serialized, and will utilize the `JsonElement`
objects to construct the JSON you ultimately want returned.

```php
class FooSerializer implements JsonSerializer
{
    public function serialize($object, PhpType $type, JsonSerializationContext $context): JsonElement
    {
        $jsonObject = new JsonObject();
        $jsonObject->addInteger('id', $object->getId());
        $jsonObject->addString('name', $object->getName());

        return $jsonObject;
    }
}
```

This bypasses all other handling for the object and sub-objects.  Once
you return the `JsonElement` it will get converted to JSON and returned.

If you have a sub-object you do not want to manually serialize, you can
pass it off to the context.

```php
class FooSerializer implements JsonSerializer
{
    public function serialize($object, PhpType $type, JsonSerializationContext $context): JsonElement
    {
        $jsonObject = new JsonObject();
        $jsonObject->addInteger('id', $object->getId());
        $jsonObject->addString('name', $object->getName());
        $jsonObject->add('foo', $context->serialize($object->getFoo()));

        return $jsonObject;
    }
}
```

This passes off serialization for the `foo` property to the library to
handle as it normally would.

Custom Deserializer
-------------------

This operates similarly to the custom serializer.  The `deserialize`
method receives a `JsonElement` and you use that to return an
instantiated object.  Delegation is available here in the same way
as the serializer.

```php
class FooDeserializer implements JsonDeserializer
{
    public function deserialize(JsonElement $jsonElement, PhpType $type, JsonDeserializationContext $context)
    {
        // we can type the JsonElement if we know it's an object
        $jsonObject = $jsonElement->asJsonObject();

        $fooObject = new FooObject();
        $fooObject->setId($jsonObject->getAsInteger('id'));
        $fooObject->setName($jsonObject->getAsString('name'));
        $fooObject->setBar($context->deserialize($jsonObject->getAsJsonObject('bar'), Bar::class));

        return $fooObject;
    }
}
```

This is operating in reverse of the custom serializer.  Here we are
getting values from the `JsonElement` and setting them to an instantiated
object.  To set `bar`, we are pulling the nested object out and passing
it to Gson to deserialize normally.

Custom Type Adapter
-------------------

The Type Adapter system is how all type handling is implemented.
Extending the `TypeAdapter` base class will require implementing both
`read` and `write` methods.  Each receives a `JsonReadable` and
`JsonWritable` respectively.

Implementing a Type Adapter is a good idea if you just want to change
the way a simple type (non-object, non-array) is serialized and
deserialized.  Alternatively, if you don't want to pay for the overhead
of a custom serializer or deserializer as it must convert to `JsonElement`
objects.

The `IntegerTypeAdapter` implementation is shown below as an example

```php
class IntegerTypeAdapter extends TypeAdapter
{
    public function read(JsonReadable $reader): ?int
    {
        if ($reader->peek() === JsonToken::NULL) {
            return $reader->nextNull();
        }

        return $reader->nextInteger();
    }

    public function write(JsonWritable $writer, $value): void
    {
        if (null === $value) {
            $writer->writeNull();

            return;
        }

        $writer->writeInteger($value);
    }
}
```

Type Adapter Factory
--------------------

If instantiating a Type Adapter is costly, you can implement a
`TypeAdapterFactory` instead.  This will allow your Type Adapter to
only be created once and will be cached on future calls during a
single request.

Two methods need to be implemented: `supports` and `create`.  Supports
will always be called first.  If `true` is returned, a call to create
will be made.

```php
class IntegerTypeAdapterFactory implements TypeAdapterFactory
{
    public function supports(PhpType $type): bool
    {
        return $type->isInteger();
    }

    public function create(PhpType $type, TypeAdapterProvider $typeAdapterProvider): TypeAdapter
    {
        return new IntegerTypeAdapter();
    }
}
```

Annotations
-----------

### @JsonAdapter

Applying this annotation will allow a custom serialization strategy
for a given property or class.  Pass the class name of any of the above
serialization methods into this annotation.
