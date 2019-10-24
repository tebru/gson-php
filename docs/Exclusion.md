Excluding Classes and Properties
================================

Another way that serialization or deserialization can be controlled is
by excluding classes or properties on classes.

Custom Exclusion Strategy
-------------------------

There are four primary interfaces for controlling if something should
be excluded:

* ClassSerializationExclusionStrategy
* PropertySerializationExclusionStrategy
* ClassDeserializationExclusionStrategy
* PropertyDeserializationExclusionStrategy

Each of these interfaces have one method that returns `true` if the
class or property should be excluded during serialization or
deserialization, depending on which interface you're implementing. One
strategy can implement multiple interfaces. Each method will also
receive metadata about the class or property to help you make the
decision.

For example, if you wanted to exclude a property from being serialized

```php
use Tebru\Gson\Exclusion\PropertySerializationExclusionStrategy;
use Tebru\Gson\PropertyMetadata;
class FooExclusionStrategy implements PropertySerializationExclusionStrategy
{
    public function skipSerializingProperty(PropertyMetadata $property): bool
    {
        return $property->getName() === 'foo';
    }

    public function shouldCache(): bool
    {
        return false;
    }
}
```

Or a class from being deserialized

```php
use Tebru\Gson\ClassMetadata;
use Tebru\Gson\Exclusion\ClassDeserializationExclusionStrategy;
class FooExclusionStrategy implements ClassDeserializationExclusionStrategy
{
    public function skipDeserializingClass(ClassMetadata $class): bool
    {
        return $class->getName() === Foo::class;
    }

    public function shouldCache(): bool
    {
        return false;
    }
}
```

Exclusion strategies implement `Cacheable`, which allows the results
to be cached between requests. This means that if you always want to
exclude a class or property, return true from `shouldCache`. If your
strategy may return different results depending on request data or the
authenticated user, return false here.

If the strategy is not cacheable, all such strategies will be run
on every class and/or property they target. This can cause performance
concerns, so a `@ExclusionCheck` annotation is provided to more directly
target which classes or properties should be checked.

Additionally, strategies may implement `SerializationExclusionDataAware`
or `DeserializationExclusionDataAware`. This will pass along
`SerializationExclusionData` or `DeserializationExclusionData` at
runtime, and will give you access to the payload, object that's being
serialized, and other contextual information like the current path. This
is not compatible with a cacheable strategy.

```php
use Tebru\Gson\Exclusion\PropertySerializationExclusionStrategy;
use Tebru\Gson\Exclusion\SerializationExclusionData;
use Tebru\Gson\Exclusion\SerializationExclusionDataAware;
use Tebru\Gson\PropertyMetadata;
class FooExclusionStrategy implements
    PropertySerializationExclusionStrategy,
    SerializationExclusionDataAware
{
    private $serializationData;

    public function skipSerializingProperty(PropertyMetadata $property): bool
    {
        return $property->getName() === 'foo'
            && $this->serializationData->getObjectToSerialize()->getFoo() !== 5;
    }

    public function shouldCache(): bool
    {
        return false;
    }

    public function setSerializationExclusionData(SerializationExclusionData $data): void
    {
        $this->serializationData = $data;
    }
}
```

Manipulating Properties on Load
-------------------------------

An option for excluding data based on runtime parameters (non-cacheable)
would be to handle `ClassMetadata` after it's loaded. Here, you can
turn on/off properties before they're passed to the type adapter.

```php
use Tebru\Gson\ClassMetadata;
use Tebru\Gson\ClassMetadataVisitor;
use Tebru\Gson\Gson;
class FooVisitor implements ClassMetadataVisitor
{
    public function onLoaded(ClassMetadata $classMetadata): void
    {
        $classMetadata->getProperty('foo')
            ->setSkipSerialize(true)
            ->setSkipDeserialize(true);
    }
}

Gson::builder()->addClassMetadataVisitor(new FooVisitor());
```

Options on the Builder
----------------------

There are also some options on the builder that affect which properties
are serialize/deserialized.

* `setVersion` - Setting a version works in conjunction with `@Since` and
  `@Until` annotations.  If a `@Since` annotation is found, properties
  will be excluded if the set version if less than or equal to the
  annotation version. Likewise, if an `@Until` annotation is found,
  properties will be excluded if the set version is greater than the
  annotation version.
* `setExcludedModifier` - This uses a bitmap of `ReflectionProperty`
  constants to determine if a property should be excluded.  By default,
  all static properties are excluded (ReflectionProperty::IS_STATIC).
  To exclude all non-public properties, you would pass in
  ```
  ReflectionProperty::IS_STATIC | ReflectionProperty::IS_PRIVATE | ReflectionProperty::IS_PROTECTED
  ```
* `requireExposeAnnotation` - Calling this on the builder will force
  a `@Expose` annotation to exist for the property or class to be
  serialized or deserialized.  It has options to limit the direction
  this annotation is enforced.
* `requireExclusionCheck` - Require the `@ExclusionCheck` annotation
  before running any of the uncacheable exclusion strategies.
* `serializeNull` - By default, nulls are not serialized.  Setting this
  will change that behavior and serialize all nulls.

Additional Annotations
----------------------

### @Exclude

Like `@Expose` this will exclude properties or classes this is applied
to.  It can also only be applied during serialization or deserialization.
If set on a class, `@Expose` annotations may be set on properties. This
will act like `requireExposeAnnotation` on a class level.

