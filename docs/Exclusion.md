Excluding Classes and Properties
================================

Another way that serialization or deserialization can be controlled is
by excluding classes or properties on classes.

Custom Exclusion Strategy
-------------------------

The primary way that this can be controlled is by creating a custom
`ExclusionStrategy`.  There are two methods that need to be implemented
on this interface.

* `shouldSkipClass` - Return true if the entire class should be skipped
* `shouldSkipProperty` - Return true if a single property should be skipped

```php
class FooExclusionStrategy implements ExclusionStrategy
{
    public function shouldSkipClass(ClassMetadata $classMetadata): bool
    {
        return Foo::class === $classMetadata->getName();
    }

    public function shouldSkipProperty(PropertyMetadata $propertyMetadata, ExclusionData $exclusionData): bool
    {
        return Foo2::class === $propertyMetadata->getDeclaringClassMetadata->getName()
            && 'bar' === $propertyMetadata->getName();
    }
}
```

The `ExclusionData` parameter allows you to make runtime exclusion
decisions based on data available during serialization or deserialization.
The `getData()` method will always return an object. This object will
be fully hydrated during serialization, but will likely be empty or
partially hydrated during deserialization unless a hydrated object was
provided when calling `Gson::fromJson()`.

During deserialization, the `getDeserializePayload()`
method will return the data that has been `json_decode`'d. During
serialization, this method will return null. Additionally, there is a
method `isSerialize()` to check if the exclusion strategy is being
executed during serialization or deserialization.

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
* `serializeNull` - By default, nulls are not serialized.  Setting this
  will change that behavior and serialize all nulls.

Additional Annotations
----------------------

### @Exclude

Like `@Expose` this will exclude properties or classes this is applied
to.  It can also only be applied during serialization or deserialization.

