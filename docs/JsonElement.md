Json Element
============

There are four classes that represent all of the different types in
JSON:

* JsonObject
* JsonArray
* JsonPrimitive
* JsonNull

All of these extend from a `JsonElement` class.  `JsonPrimitive` can
be an integer, float, string, or boolean.  On all of the classes, there
are convenience methods to help determine what kind of type it is.

You will use these classes when working with custom serialization or
deserialization.
