Change Log
==========

This document keeps track of changes between releases of the library.

v0.5.9
------

* FIXED: Issue with strtolower type check in docblock

v0.5.8
------

* FIXED: Issue with `@Type` array not overriding docblocks

v0.5.7
------

* FIXED: Issue with `mixed` type hint

v0.5.6
------

* ADDED: Ability to change type checking strictness for registered types
* ADDED: Discriminator interface to handle polymorphic deserializations
* ADDED: Additional type check of docblocks

v0.5.5
------

* FIXED: DateTimeTypeAdapter now works for any DateTimeInterface

v0.5.4
------

* CHANGE: Default cache to chain cache with array and php file cache

v0.5.3
------

* ADDED: `@VirtualProperty` value will be used if `@SerializedName` doesn't exist
* ADDED: Ability to set custom cache on builder
* CHANGE: Default cache to php file cache

v0.5.2
------

* FIXED: TypeError when calling getters for non-nullable return types

v0.5.1
------

* ADDED: Support for Symfony 4 components
* ADDED: Ability to switch property name transformations by common policies on builder

v0.5.0
------

* BC BREAK: Modified ExclusionStrategy interface to accept ExclusionData during class checks

v0.4.1
------

* CHANGE: Switching cache from Doctrine to PSR simple cache
* CHANGE: Updating annotation reader library to support setting
annotation values
* ADDED: Class target to `@VirtualProperty` to allow wrapping/unwrapping classes in generic data bags
* ADDED: `toArray` and `fromArray` convenience methods on `Gson`

v0.4.0
------

* BC BREAK: Adding link between ClassMetadata and PropertyMetadata
* BC BREAK: Removed AnnotationSet and replaced with AnnotationCollection.
This affects exclusion strategies using annotations from ExclusionData.
* ADDED: Strict type checking

v0.3.0
------

* BC BREAK: Changed ExclusionStrategy interface to accept ExclusionData for access to runtime data
* BC BREAK: Added `getPayload()` method to JsonReadable interface
* FIXED: Issues with default property values and overrides when deserializing into provided object

v0.2.0
------

* BC BREAK: Refactored ExclusionStrategy interface
* BC BREAK: Setting a cache directory no longer enables caching, use enableCache() method on builder
* BC BREAK: Removed ArrayList/HashMap type adapters
* BC BREAK: Removed support for 'JsonElement' type, must use full class name
* BC BREAK: Abstracted PHP Type to new library, changed class name to \Tebru\PhpType\TypeToken
* BC BREAK: `@Type` annotation no longer supports `options` key
* BC BREAK: Changed thrown exception in multiple places
* BC BREAK: Added getPath() method on JsonReadable interface
* FIXED: Infinite loop on circular references when excluding property
* FIXED: Now throws exception for json that can't be properly decoded
* FIXED: Now throws exception for invalid array key types
* FIXED: Some issues where types were potentially not cast to the proper value
* FIXED: Issue with InstanceCreator not creating correct object
* FIXED: Setters now set null values
* ADDED: Support for subclasses of DateTime
* ADDED: toJsonElement() method on Gson
* ADDED: Path information for thrown exception during reading
* ADDED: DateTime format to GsonBuilder

v0.1.0
------

Initial release!
