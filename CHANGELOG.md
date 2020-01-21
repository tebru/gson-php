Change Log
==========

This document keeps track of changes between releases of the library.

master
------

* FIXED: Don't error if using custom cache without directory
* FIXED: deprecated ReflectionType::__toString no longer called

v0.7.1
------
* FIXED: Fixed issue with null value in array

v0.7.0
------
* BC BREAK: Combined TypeAdapterFactory methods into a nullable create
* BC BREAK: Removed JsonElement
* BC BREAK: Removed Readable/Writable and json readers/writers
* BC BREAK: Changed Discriminator definition
* BC BREAK: Changed JsonSerializer/JsonDeserializer definition
* BC BREAK: Changed JsonSerializationContext/JsonDeserializationContext definition
* BC BREAK: Changed TypeAdapter definition
* BC BREAK: Moved serializeNull to Context
* CHANGE: Moved TypeAdapters out of internal namespace and removed final
* ADDED: ReaderContext and WriterContext

v0.6.6
------
* ADDED: Support for listening/manipulating property collection on load
* ADDED: Option to only run exclusion strategies by annotation

v0.6.5
------
* ADDED: PHPStorm metadata to help with auto completion
* CHANGE: Allow `@Expose` property in `@Exclude` class

v0.6.4
------
* FIXED: Serializing stdClass with array adapter

v0.6.3
------
* FIXED: Docblock parsing continues checking type locations for generic array

v0.6.2
------

* FIXED: Wildcard type adapter checks for interface

v0.6.1
------

* Allowing type override during serialization

v0.6.0
------

* Various performance improvements
* BC BREAK: Modified Reflection and Array type adapter constructors
* BC BREAK: Removed DefaultClassMetadata::addPropertyMetadata()
* BC BREAK: Removed support for grouped use statements in type guessing
* FIXED: Writing to JsonElement respects serializeNull setting
* ADDED: getPath() to JsonWritable
* ADDED: Split ExclusionStrategy into multiple specific interfaces
* ADDED: deprecations for ExclusionStrategy/ExclusionData
* ADDED: Payload on exception decoding json data
* ADDED: Type check on property default
* ADDED: phpdocumentor/reflection-docblock dependency
* CHANGE: Order of type guessing
* CHANGE: toJsonElement() now uses json element writer

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
