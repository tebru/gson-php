Customizing Serialization/Deserialization
=========================================

There are multiple ways that serialization or deserialization can be
customized.

Custom Serializer
-----------------

By implementing the `JsonSerializer` interface, you can completely
control how serialization is accomplished.  You will receive the
object that should be serialized, and will return something that can
be passed into `json_encode`.

```php
use Tebru\Gson\JsonSerializationContext;
use Tebru\Gson\JsonSerializer;
use Tebru\PhpType\TypeToken;
class FooSerializer implements JsonSerializer
{
    public function serialize($object, TypeToken $type, JsonSerializationContext $context)
    {
        return [
            'id' => $object->getId(),
            'name' => $object->getName(),
        ];
    }
}
```

This bypasses all other handling for the object and sub-objects.

If you have a sub-object you do not want to manually serialize, you can
pass it off to the context.

```php
use Tebru\Gson\JsonSerializationContext;
use Tebru\Gson\JsonSerializer;
use Tebru\PhpType\TypeToken;
class FooSerializer implements JsonSerializer
{
    public function serialize($object, TypeToken $type, JsonSerializationContext $context)
    {
        return [
            'id' => $object->getId(),
            'name' => $object->getName(),
            'foo' => $context->serialize($object->getFoo()),
        ];
    }
}
```

This passes off serialization for the `foo` property to the library to
handle as it normally would.

Custom Deserializer
-------------------

This operates similarly to the custom serializer.  The `deserialize`
method receives data from `json_decode` and you use that to return an
instantiated object.  Delegation is available here in the same way
as the serializer.

```php
use Tebru\Gson\JsonDeserializationContext;
use Tebru\Gson\JsonDeserializer;
use Tebru\PhpType\TypeToken;
class FooDeserializer implements JsonDeserializer
{
    public function deserialize($value, TypeToken $type, JsonDeserializationContext $context)
    {
        $fooObject = new FooObject();
        $fooObject->setId($value['id']);
        $fooObject->setName($value['name']);
        $fooObject->setBar($context->deserialize($value['bar'], Bar::class));

        return $fooObject;
    }
}
```

This is operating in reverse of the custom serializer.  Here we are
getting values and setting them to an instantiated object.  To set
`bar`, we are pulling the nested object out and passing it to Gson
to deserialize normally.

Custom Type Adapter
-------------------

The Type Adapter system is how all type handling is implemented.
Extending the `TypeAdapter` base class will require implementing both
`read` and `write` methods. Each method receives the data and context.

Implementing a Type Adapter is a good idea if you just want to change
the way a simple type (non-object, non-array) is serialized and
deserialized.

The `IntegerTypeAdapter` implementation is shown below as an example

```php
use Tebru\Gson\Context\ReaderContext;
use Tebru\Gson\Context\WriterContext;
use Tebru\Gson\TypeAdapter;
class IntegerTypeAdapter extends TypeAdapter
{
    public function read($value, ReaderContext $context): ?int
    {
        return $value === null ? null : (int)$value;
    }

    public function write($value, WriterContext $context): ?int
    {
        return $value === null ? null : (int)$value;
    }
}
```

Type Adapter Factory
--------------------

If instantiating a Type Adapter is costly, you can implement a
`TypeAdapterFactory` instead.  This will allow your Type Adapter to
only be created once and will be cached on future calls during a
single request.

Return null from create if the type adapter does not support the
provided type.

```php
use Tebru\Gson\Internal\TypeAdapterProvider;
use Tebru\Gson\TypeAdapter;
use Tebru\Gson\TypeAdapter\IntegerTypeAdapter;
use Tebru\Gson\TypeAdapterFactory;
use Tebru\PhpType\TypeToken;
class IntegerTypeAdapterFactory implements TypeAdapterFactory
{
    public function create(TypeToken $type, TypeAdapterProvider $typeAdapterProvider): ?TypeAdapter
    {
        return $type->isInteger() ? new IntegerTypeAdapter() : null;
    }
}
```

Annotations
-----------

### @JsonAdapter

Applying this annotation will allow a custom serialization strategy
for a given property or class.  Pass the class name of any of the above
serialization methods into this annotation.
