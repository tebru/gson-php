# Advanced Usage

Use the `GsonBuilder` to handle all configuration of how serialization
or deserialization should be accomplished.

Adding Type Adapter Factories
-----------------------------

These will be checked before most of the default type adapters.

```php
use Tebru\Gson\Gson;
Gson::builder()
    ->addTypeAdapterFactory(new FooTypeAdapterFactory())
    ->build();
```

Adding Type Adapters, Serializers, or Deserializers
---------------------------------------------------

```php
use Tebru\Gson\Gson;
Gson::builder()
    ->registerType('array', new MyBetterArrayTypeAdapter())
    ->build();
```

```php
use Tebru\Gson\Gson;
Gson::builder()
    ->registerType(Foo::class, new FooSerializer())
    ->build();
```

```php
use Tebru\Gson\Gson;
Gson::builder()
    ->registerType(Bar::class, new BarDeserializer())
    ->build();
```

By default, if you register a class, all children will also by
registered for that type. By passing in `true` as the 3rd argument, you
can enable stricter type checking so that only exactly class matches
will be registered.

```php
use Tebru\Gson\Gson;
Gson::builder()
    ->registerType(MyBaseClass::class, new FooDeserializer(), true)
    ->build();
```

One example of this being useful is when using custom deserializers. You
can specify a base class or interface as the registered type, then
within the deserializer, delegate deserialization for the concrete.

```php
use Tebru\Gson\JsonDeserializationContext;
use Tebru\Gson\JsonDeserializer;
use Tebru\PhpType\TypeToken;
class FooDeserializer implements JsonDeserializer
{
    public function deserialize($value, TypeToken $type, JsonDeserializationContext $context)
    {
        return $value['property']
            ? $context->deserialize($value, Foo::class)
            : $context->deserialize($value, Bar::class);
    }
}
```

Adding Discriminators
---------------------

Discriminators leverage the custom deserialization system. Implementing
it and adding it to the builder provides a simpler interface for
choosing how to deserialize polymorphics.

```php
use Tebru\Gson\Gson;
Gson::builder()
    ->addDiscriminator(BaseClass::class, new FooDiscriminator())
    ->build();
```

```php
use Tebru\Gson\Discriminator;
class FooDiscriminator implements Discriminator
{
    public function getClass($object): string
    {
        switch ($object['status']) {
            case 'foo':
                return Child1::class;
            case 'bar':
                return Child2::class;
        }
    }
}
```

Add an Instance Creator
-----------------------

This will provide a way to customize instantiation of classes.

```php
use Tebru\Gson\Gson;
Gson::builder()
    ->addInstanceCreator(FooBar::class, new FooBarInstanceCreator())
    ->build();
```

Set the DateTime format
-----------------------

Formatting DateTimes defaults to using DateTime::ATOM

```php
use Tebru\Gson\Gson;
Gson::builder()
    ->setDateTimeFormat(DateTime::RFC2822)
    ->build();
```

Set the Version
---------------

Combined with the `@Since` and `@Until` annotations, you can use
the same class with difference versions of an api, for example.

```php
Gson::builder()
    ->setVersion('1.0')
    ->build();
```

Set Excluded Modifiers
----------------------

This defines properties that are excluded from serialization based
on their visibility.

```php
use Tebru\Gson\Gson;
Gson::builder()
    ->setExcludedModifier(ReflectionProperty::IS_PROTECTED)
    ->build();
```

Require the Expose Annotation
-----------------------------

This will exclude everything without an `@Expose` annotation.

```php
use Tebru\Gson\Gson;
Gson::builder()
    ->requireExposeAnnotation()
    ->build();
```

Add Exclusion Strategy
----------------------

You must specify whether it should be enabled for serialization,
deserialization, or both.

```php
use Tebru\Gson\Gson;
Gson::builder()
    ->addExclusionStrategy(new FooExclusionStrategy(), true, true)
    ->build();
```

Require exclusion check
-----------------------

You can have gson only check exclusion strategies for properties/classes
with the `@ExclusionCheck` annotation.

```php
use Tebru\Gson\Gson;
Gson::builder()
    ->requireExclusionCheckAnnotation()
    ->build();
```

Skip scalar type adapters
-------------------------

If you don't need to do anything special with scalar types, you can
tell Gson to skip parsing them. This will skip calling type adapters
for `int`, `float`, `string`, `bool`, and `null`.

```php
use Tebru\Gson\Gson;
Gson::builder()
    ->setEnableScalarAdapters(false)
    ->build();
```

Reader/Writer Contexts
----------------------

Contexts can now be defined during reading and writing and set on the
builder at compile time. You can set custom attributes to use for
custom type adapters.

```php
use Tebru\Gson\Gson;
use Tebru\Gson\Context\WriterContext;
$writerContext = new WriterContext();
$writerContext->setSerializeNull(true);
$writerContext->setAttribute('foo', 'bar');

Gson::builder()
    ->setWriterContext($writerContext)
    ->build();
```

Enable Cache
------------

To enable the cache, set a cache directory and set the enable cache flag
to true.

```php
use Tebru\Gson\Gson;
Gson::builder()
    ->enableCache(true)
    ->setCacheDir('/tmp')
    ->build();
```

Change Property Naming
----------------------

By default, all property names are converted to snake_case during
serialization, but you can override this behavior by setting a new
PropertyNamingPolicy to the builder.

```php
use Tebru\Gson\Gson;
use Tebru\Gson\PropertyNamingPolicy;
Gson::builder()
    ->setPropertyNamingPolicy(PropertyNamingPolicy::IDENTITY)
    ->build();
```

Here's a list of what's supported:

- IDENTITY: Leave property name unchanged
- LOWER_CASE_WITH_DASHES: Convert camel case to lower case separated by dashes
- LOWER_CASE_WITH_UNDERSCORES: Convert camel case to lower case separated by underscores
- UPPER_CASE_WITH_DASHES: Convert camel case to upper case separated by dashes
- UPPER_CASE_WITH_UNDERSCORES: Convert camel case to upper case separated by underscores
- UPPER_CAMEL_CASE: Capitalize the first letter of a camel case property
- UPPER_CAMEL_CASE_WITH_SPACES: Converts camel case to capitalized words

You can customize this further by implementing a
`PropertyNamingStrategy` and adding it to the builder.

```php
use Tebru\Gson\Gson;
Gson::builder()
    ->setPropertyNamingStrategy(new MyCustomPropertyNamingStrategy())
    ->build();
```

Add Method Naming Strategy
--------------------------

By default, method names will be property names prepended with `get`,
`is`, or `set`.  Override this by implementing the `MethodNamingStrategy`
and add it to the builder.

```php
use Tebru\Gson\Gson;
Gson::builder()
    ->setMethodNamingStrategy(new SameAsPropertyNameStrategy())
    ->build();
```
