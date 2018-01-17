Annotation Reference
====================

This page lists all of the annotations and how to use them.

Make sure you're importing the annotations as well. Although all of the
examples use the regular annotation, here's an example of using an alias.

```
use Tebru\Gson\Annotation as Gson;

/**
 * @Gson\Type("string")
 */
```

@Accessor
---------

Explicitly defines a getter or setter mapping.  Use the `get` or `set`
key to point to a method.

```php
/**
 * @Accessor(get="myCustomGetter", set="myCustomSetter")
 */
private $foo;
```

@Exclude
--------

Used to exclude a class or property from serialization or deserialization.

Exclude a property for both serialization and deserialization

```php
/**
 * @Exclude()
 */
private $foo
```

Exclude a class only during serialization

```php
/**
 * @Exclude(deserialize=false)
 */
class Foo {}
```

@Expose
-------

Only used in conjunction with `requireExposeAnnotation()` on the builder.
Can also be used for a single direction.

Expose a property

```php
/**
 * @Expose()
 */
private $foo
```

Exclude a class only during deserialization

```php
/**
 * @Expose(serialize=false)
 */
class Foo {}
```

@JsonAdapter
------------

Used to specify a custom Type Adapter, Type Adapter Factory, Json
Serializer, or Json Deserializer.  Can be used on either a class or a
property.

```php
/**
 * @JsonAdapter("My\Custom\ClassTypeAdapter")
 */
class Foo {
    /**
     * @JsonAdapter("My\Custom\ClassTypeAdapterFactory")
     */
    private $bar;

    /**
     * @JsonAdapter("My\Custom\ClassSerializer")
     */
    private $baz;

    /**
     * @JsonAdapter("My\Custom\ClassDeserializer")
     */
    private $qux;
}
```

@SerializedName
---------------

Allows overriding the property name that appears in JSON.

```php
/**
 * @SerializedName("bar")
 */
private $foo
```

@Since
------

Specifies when a property, class, or method was added.  If this number
is greater than or equals the current version, the property will be
excluded.

```php
/**
 * @Since("2.0")
 */
private $foo
```

@Type
-----

Overrides the type of a property.  If this annotation exist, the type
will be used before it is guessed.

```php
/**
 * @Type("DateTime")
 */
private $foo
```

@Until
------

Specifies when a property, class, or method will be removed.  If this
number is less than the current version, the property will be excluded.

```php
/**
 * @Until("2.0")
 */
private $foo
```

@VirtualProperty
----------------

This acts as an identifier on methods.  It's purpose is to add
additional data during serialization.  It does nothing during
deserialization.

```php
/**
 * @VirtualProperty("foo")
 * @Type("int")
 */
public function getFoo()
{
    return $this->foo;
}
```

The value is the serialized name. It can be used in conjunction with
other annotations.

```php
/**
 * @VirtualProperty()
 * @SerializedName("foo")
 * @Type("int")
 */
public function getFoo()
{
    return $this->foo;
}
```

If `@SerializedName` exists, that value will be used instead.

It may also be used on classes to wrap/unwrap a class in a key

```php
/**
 * @VirtualProperty("data")
 */
class User
{
    public $id;
    public $name;
}
```

This will allow deserialization of the following data into `User`

```json
{
    "data": {
        "id": 1,
        "name": "John Doe"
    }
}
```

And will serialize `User` into the above json.
