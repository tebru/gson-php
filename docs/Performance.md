# Performance

There are a few steps you can take to make sure Gson is fast in
production.

Caching
-------

The easiest way is to add caching.

```php
use Tebru\Gson\Gson;
Gson::builder()
    ->enableCache(true)
    ->setCacheDir('/tmp')
    ->build();
```

You can also add your own cache object

```php
use Symfony\Component\Cache\Simple\RedisCache;
use Tebru\Gson\Gson;
Gson::builder()
    ->enableCache(true)
    ->setCache(new RedisCache($redis))
    ->build();
```

Skip scalar type parsing
------------------------

You can configure gson to ignore parsing scalar types. This will
ignore all `int`, `float`, `string`, `bool`, and `null` types.

```php
use Tebru\Gson\Gson;
Gson::builder()
    ->setEnableScalarAdapters(false)
    ->build();
```


Exclusion Strategies
--------------------

Determining exclusion at runtime can be costly when it needs to be done
for a large number of properties.

If your exclusion strategy doesn't need to be run at runtime, return
`true` from `ExclusionStrategy::shouldCache()`.

If it does, you can improve performance by requiring the
`@ExclusionCheck` annotation.

```php
use Tebru\Gson\Gson;
Gson::builder()
    ->requireExclusionCheckAnnotation()
    ->build();
```

This will skip checking the configured exclusion strategies for any
class/property that doesn't have this annotation.

Additionally, you can configure what's checked through different
interfaces. For example, if you only need to exclude properties during
serialization, only implement the
`PropertySerializationExclusionStrategy` interface.

More information is available at
[Excluding Classes and Properties](docs/Exclusion.md)

Visitors
--------

Instead of using an exclusion strategy, you might be able to make use
of the `ClassMetadataVisitor`. This will get passed `ClassMetadata`
once when it's loaded for the first time. At that point, you can iterate
over the properties and ignore them during serialization and/or
deserialization. This is useful if you don't need access to the specific
object/payload at runtime.

Type Adapters vs JsonSerializer
-------------------------------

There is a tiny performance hit to using `JsonSerializer` and
`JsonDeserializer` as they get wrapped in a type adapter and called
from there. It's unlikely to matter much, but if it's just as easy to
implement a `TypeAdapter`, do that instead.
