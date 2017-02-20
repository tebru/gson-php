Change Log
==========

This document keeps track of changes between releases of the library.

Master
------

* BC BREAK: Refactored ExclusionStrategy interface
* BC BREAK: Setting a cache directory no longer enables caching, use enableCache() method on builder
* BC BREAK: Removed ArrayList/HashMap type adapters
* BC BREAK: Removed support for 'JsonElement' type, must use full class name
* FIXED: Infinite loop on circular references when excluding property
* ADDED: Support for subclasses of DateTime
* ADDED: toJsonElement() method on Gson

v0.1.0
------

Initial release!
